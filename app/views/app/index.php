<div id="notification"></div>
<div id="app"></div>

<script>
class App {
	constructor(elementSelector) {
		this.sesskey = ''
		this.element = $(elementSelector)
		this.action = {
		  courses: "core_course_get_enrolled_courses_by_timeline_classification",
		  deadlines: "core_calendar_get_action_events_by_timesort"
		}
	}

	renderLoginPage() {
		this.element.html(`
		  <h1>Welcome</h1>
		  <button id="btn-start">Start</button>
		`)

		// Get logintoken
		$('#btn-start').click(() => {
		  $.ajax({
			url: '/proxy/<?= $api['domain'] . $api['login'] ?>',
			method: 'GET',
			dataType: 'html',
			beforeSend: () => {
			  $('#btn-start').prop('disabled', true)
			  $('#notification').html('Logging in...')
			},
			success: (response) => {
			  const loginToken = $(response).find('[name="logintoken"]').val()
			  const username = $('#form-login input[name="username"]').val()
			  const password = $('#form-login input[name="password"]').val()
			  let loginData = {
				anchor: '',
				logintoken: loginToken,
				username: <?= $api['username'] ?>,
				password: <?= $api['password'] ?>
			  }

			  // Send login credentials
			  loginData = $.param(loginData)
			  $.ajax({
				url: '/proxy/<?= $api['domain'] . $api['login'] ?>',
				method: 'POST',
				contentType: 'application/x-www-form-urlencoded',
				data: loginData,
				success: (response, textStatus, xhr) => {
				  const moodleSession = Cookies.get('MoodleSession')
				  $.ajax({
					url: '/proxy/<?= $api['domain'] . $api['login'] ?>',
					method: 'GET',
					dataType: 'html',
					beforeSend: () => {$('#notification').html('Submitting credentials...')},
					success: (response) => {
					  $('#btn-start').prop('disabled', false)
					  Cookies.set('sesskey', this.getSesskey(response))
					  return this.REFRESH()
					},
					error: (xhr, status, error) => {
					  $('#notification').html('Failed to retrieve sesskey')
					}
				  });
				},
				error: (xhr, status, error) => {
				  $('#notification').html('Failed to log in')
				}
			  })
			},
			error: (xhr, status, error) => {
			  $('#notification').html('Failed to connect to the remote API')
			  $('#btn-start').prop('disabled', false)
			}
		  })
		})
	}

	renderAppPage() {
		this.pushNotification()

		this.element.html(`
		  <h1>Dashboard</h1><br>
		  <button id="btn-logout">Logout</button><br><br><br>
		  <h2>Deadlines</h2>
		  <button id="btn-deadlines">Fetch deadlines</button>
		  <hr>
		  <div class="card-container" id="fetch-deadlines"></div>
		  <hr><br><br><br>
		  <h2>Courses</h2>
		  <button id="btn-courses">Fetch courses</button>
		  <hr>
		  <div class="card-container" id="fetch-courses"></div>
		  <hr>
		`)

		this.sesskey = Cookies.get('sesskey')
		this.element.on('click', 'button', (event) => {
		  switch ($(event.currentTarget).attr('id')) {
			case 'btn-deadlines': return this.fetchDeadlines()
			case 'btn-courses': return this.fetchCourses()
			case 'btn-logout': return this.logout()
			default: break;
		  }
		})

	}

	pushNotification() {
		// Check if the browser supports notifications
		if ('Notification' in window) {
		// Request permission from the user
		Notification.requestPermission().then(function(permission) {
			if (permission === 'granted') {
			// Create a notification
			var notification = new Notification('Push Notification', {
				body: 'Hello, world!',
				tag: 'custom-notification',
				silent: true // Mutes the notification sound
			});

			// Handle notification click event
			notification.onclick = function() {
				// Handle the click event
				console.log('Notification clicked.');
			};
			}
		});
		} else {
		alert('Notifications are not supported in this browser!');
		}
	}

	checkAuthentication() {
		Cookies.get('MoodleSession') ?? Cookies.set('MoodleSession', 'DEFAULT')
		$.ajax({
		  url: '/proxy/<?= $api['domain'] . $api['service'] ?>',
		  method: 'POST',
		  dataType: 'json',
		  beforeSend: () => {this.element.html('Connecting to Moodle API...')},
		  data: JSON.stringify([{"methodname": "<?= $api['action'] ?>"}]),
		  success: (response) => {
			if (response[0].error === true && response[0].exception.errorcode === "servicerequireslogin") {
			  this.renderLoginPage()
			  if (Cookies.get('sesskey')) {
				$('#notification').append('Wrong username or password!')
				Cookies.remove('sesskey')
			  }
			} else {
			  this.renderAppPage()
			}
		  },
		  error: () => {
			this.element.html('You are OFFLINE!')
		  }
		});
	}

	logout() {
		$.ajax({
		  url: `/proxy/<?= $api['domain'] . $api['logout'] ?>?sesskey=${this.sesskey}`,
		  method: 'GET',
		  beforeSend: () => {this.element.html('Logging out, please wait...')},
		  success: () => {
			for (let cookie in Cookies.get()) { Cookies.remove(cookie) }
			return this.REFRESH()
		  },
		  error: (xhr, status, error) => {
			return $('#notification').html('Failed to connect to the remote API')
		  }
		})
	}

	fetchCourses() {
		return $.ajax({
		  url: `/proxy/<?= $api['domain'] . $api['service'] ?>?sesskey=${this.sesskey}&info=${this.action.courses}`,
		  method: 'POST',
		  contentType: 'application/json',
		  dataType: 'json',
		  beforeSend: () => {$('#fetch-courses').html('Loading...')},
		  data: JSON.stringify([{"index":0,"methodname":this.action.courses,"args":{"offset":0,"limit":0,"classification":"all","sort":"fullname"}}]),
		  success: (response) => {
			$('#fetch-courses').html('')
			$.each(response[0].data.courses, (index, course) => {
	  		  $('#fetch-courses').append(`
				<div class="card">
				  <div class="card-image" style="background-image:url('${course.courseimage}')"></div>
				  <div class="card-content">
				    <h3 class="card-title">${course.fullname}</h3>
				    <p class="card-description">${course.summary}</p>
				  </div>
				</div>
			  `)
			})
		  },
		  error: (jqXHR, textStatus, errorThrown) => {
			$('#notification').text(errorThrown)
		  }
		})
	}

	fetchDeadlines() {
		let currentDate = new Date()
		currentDate.setHours(0, 0, 0, 0)
		let timeSortFrom = Math.floor(currentDate.getTime() / 1000)
		let timeSortTo = Math.floor((currentDate.getTime() + 10 * 24 * 60 * 60 * 1000) / 1000)
		return $.ajax({
		  url: `/proxy/<?= $api['domain'] . $api['service'] ?>?sesskey=${this.sesskey}&info=${this.action.deadlines}`,
		  method: 'POST',
		  beforeSend: () => {$('#fetch-deadlines').html('Loading...')},
		  data: JSON.stringify([{"index":0,"methodname":this.action.deadlines,"args":{"limitnum":26,"timesortfrom":timeSortFrom,"timesortto":timeSortTo,"limittononsuspendedevents":true}}]),
		  success: (response) => {
			$('#fetch-deadlines').html('');
			$.each(response[0].data.events, (index, event) => {
			  let date = new Date(event.timesort * 1000);
			  let deadline = date.toLocaleString(undefined, {
				weekday: 'long',
				day: 'numeric',
				month: 'short',
				year: 'numeric'
			  })
			  $('#fetch-deadlines').append(`
				<div class="card">
				  <h5>${deadline}</h5>
				  <div class="card-image" style="background-image:url('${event.course.courseimage}')"></div>
				  <div class="card-content">
				    <h3 class="card-title">${event.course.fullname}</h3>
				    <p class="card-description">${event.course.summary}</p>
				  </div>
				</div>
			  `)
			})
		  },
		  error: (jqXHR, textStatus, errorThrown) => {
			$('#notification').text(errorThrown);
		  }
		})
	}

	REFRESH() {
		return location.replace(location.href)
	}

	getSesskey(response) {
		let parser = new DOMParser()
		let doc = parser.parseFromString(response, 'text/html')
		let scriptTags = doc.getElementsByTagName('script')
		let sesskey = 'DEFAULT'

		for (let i = 0; i < scriptTags.length; i++) {
		  let scriptTag = scriptTags[i]
		  let scriptContent = scriptTag.textContent || scriptTag.innerText

		  if (scriptContent.includes('M.cfg')) {
			let regex = /"sesskey":"([^"]+)"/
			let match = regex.exec(scriptContent)

			  if (match && match[1]) {
				sesskey = match[1]
				break
			  }
		  }
		}

		return sesskey
	}
}

const app = new App($('#app'))
app.checkAuthentication()
</script>