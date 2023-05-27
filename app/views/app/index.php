<div id="notification"></div>
<div id="app"></div>

<script>
	const APP = $('#app')

	function renderLoginPage() {
	APP.html(`
		<h1>Login</h1>
		<form id="form-login">
			<input type="text" name="username" placeholder="Username" required autofocus>
			<br>
			<input type="text" name="password" placeholder="Password" required>
			<br>
			<button type="submit" id="btn-login">Login</button>
		</form>
	`)

	// Get logintoken
	$('#form-login').submit(function(event) {
		event.preventDefault()
		$.ajax({
			url: '/proxy/<?= $api['domain'] . $api['login'] ?>',
			method: 'GET',
			dataType: 'html',
			beforeSend: () => {
				$('#btn-login').prop('disabled', true)
				$('#notification').html('Logging in...')
			},
			success: function(response) {
				const loginToken = $(response).find('[name="logintoken"]').val()
				const username = $('#form-login input[name="username"]').val()
				const password = $('#form-login input[name="password"]').val()
				let loginData = {
					anchor: '',
					logintoken: loginToken,
					username: username,
					password: password
				}

				// Post login credentials
				loginData = $.param(loginData)
				$.ajax({
					url: '/proxy/<?= $api['domain'] . $api['login'] ?>',
					method: 'POST',
					contentType: 'application/x-www-form-urlencoded',
					data: loginData,
					success: function(response, textStatus, xhr) {
						const moodleSession = Cookies.get('MoodleSession')
						$.ajax({
							url: '/proxy/<?= $api['domain'] . $api['login'] ?>',
							method: 'GET',
							dataType: 'html',
							beforeSend: () => {
								$('#notification').html('Submitting credentials...')
							},
							success: function(response) {
								$('#btn-login').prop('disabled', false)
								Cookies.set('sesskey', getSesskey(response))
								return REFRESH()
							},
							error: function(xhr, status, error) {
								$('#notification').html('Failed to retrieve sesskey')
							}
						})
					},
					error: function(xhr, status, error) {
						$('#notification').html('Failed to log in')
					}
				})
			},
			error: function(xhr, status, error) {
				$('#notification').html('Failed to connect to the remote API')
				$('#btn-login').prop('disabled', false)
			}
		})

		function getSesskey(response) {
			let parser = new DOMParser()
			let doc = parser.parseFromString(response, 'text/html')
			let scriptTags = doc.getElementsByTagName('script')
			let sesskey = 'blank'

			for (let i = 0; i < scriptTags.length; i++) {
				let scriptTag = scriptTags[i]
				let scriptContent = scriptTag.textContent || scriptTag.innerText

				if (scriptContent.includes('M.cfg')) {
					let regex = /"sesskey":"([^"]+)"/
					let match = regex.exec(scriptContent)

					if (match && match[1]) {
						sesskey = match[1]
						break;
					}
				}
			}

			return sesskey
		}

	})
	}

	function renderAppPage() {
		APP.html(`
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
		const sesskey = Cookies.get('sesskey')
		APP.on('click', 'button', function() {
			switch ($(this).attr('id')) {
				case 'btn-deadlines': return fetchDeadlines(); break;
				case 'btn-courses': return fetchCourses(); break;
				case 'btn-logout':
					for ( let cookie in Cookies.get() ) { Cookies.remove(cookie) }
					return REFRESH(); break;
				default: break;
			}
		})

		function fetchCourses() {
			return $.ajax({
				url: `/proxy/<?= $api['domain'] . $api['service'] ?>?sesskey=${sesskey}&info=core_course_get_enrolled_courses_by_timeline_classification`,
				method: 'POST',
				contentType: 'application/json',
				dataType: 'json',
				beforeSend: () => {$('#fetch-courses').html('Loading. . .')},
				data: JSON.stringify([{"index":0,"methodname":"core_course_get_enrolled_courses_by_timeline_classification","args":{"offset":0,"limit":0,"classification":"all","sort":"fullname"}}]),
				success: function(response) {
					$('#fetch-courses').html('')
					$.each(response[0].data.courses, function(index, course) {
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
				error: function(jqXHR, textStatus, errorThrown) {
					console.error(errorThrown)
					$('#notification').html(errorThrown)
				}
			})
		}

		function fetchDeadlines() {
			// Get the current date in the local machine's timezone
			let currentDate = new Date();
			// Set the time of currentDate to 12:00:00 AM
			currentDate.setHours(0, 0, 0, 0);
			// Get the timestamp of variable A (currentDate)
			let timeSortFrom = Math.floor(currentDate.getTime() / 1000);
			// Get the timestamp of variable B (10 days from currentDate)
			let timeSortTo = Math.floor((currentDate.getTime() + 10 * 24 * 60 * 60 * 1000) / 1000);
			return $.ajax({
				url: `/proxy/<?= $api['domain'] . $api['service'] ?>?sesskey=${sesskey}&info=core_calendar_get_action_events_by_timesort`,
				method: 'POST',
				beforeSend: () => {$('#fetch-deadlines').html('Loading. . .')},
				data: JSON.stringify([{"index":0,"methodname":"core_calendar_get_action_events_by_timesort","args":{"limitnum":26,"timesortfrom":timeSortFrom,"timesortto":timeSortTo,"limittononsuspendedevents":true}}]),
				success: function(response) {
					$('#fetch-deadlines').html('')
					$.each(response[0].data.events, function(index, event) {
					  // Convert timestamp to a Date object
					  let date = new Date(event.timesort * 1000);
				      // Format the date string
					  let deadline = date.toLocaleString(undefined, {
						weekday: 'long', // Full weekday name (e.g., Monday)
						day: 'numeric', // Day of the month (e.g., 27)
						month: 'short', // Abbreviated month name (e.g., May)
						year: 'numeric' // 4-digit year (e.g., 2023)
					  });
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
				error: function(jqXHR, textStatus, errorThrown) {
					console.error(errorThrown)
					$('#notification').html(errorThrown)
				}
			})
		}
	}

	function checkAuthentication() {
		Cookies.get('MoodleSession') ?? Cookies.set('MoodleSession', 'DEFAULT')
		$.ajax({
			url: '/proxy/<?= $api['domain'] . $api['service'] ?>',
			method: 'POST',
			dataType: 'json',
			beforeSend: () => {APP.html('Connecting to API. . .')},
			data: JSON.stringify([{"methodname": "core_course_get_enrolled_courses_by_timeline_classification"}]),
			success: function(response) {
				if ( response[0].error === true && response[0].exception.errorcode === "servicerequireslogin" ) {
					renderLoginPage()
					if ( Cookies.get('sesskey') ) {
						$('#notification').append('Wrong username or password!')
						Cookies.remove('sesskey')
					}
				} else {
					renderAppPage()
				}
			},
			error: function() {
                $('#notification').append('You are OFFLINE!')
			}
		})
	}

	function REFRESH() {
		return location.replace(location.href)
	}

	checkAuthentication()
</script>