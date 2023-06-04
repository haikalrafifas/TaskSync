<h1>Welcome to <?= APP_NAME ?>!</h1>
<p>Please fill this form to complete the installation.</p>
<div id="notification"></div>
<form id="form-setup">
    <label for="domain">Moodle Domain:</label><br>
    <input type="text" id="domain" name="domain" placeholder="https://example.com/" required autofocus><br><br>
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" placeholder="Username" required autocomplete="off"><br><br>
    <label for="password">Password:</label><br>
    <input type="text" id="password" name="password" placeholder="Password" required><br><br>
    <input type="checkbox" name="agreement" required>I agree to the terms and conditions.<br><br>
    <button id="btn-done" type="submit">Done</button>
</form>

<script>
$('#form-setup').submit(function(event) {
    event.preventDefault()
    const formData = $(this).serialize()
    $.ajax({
      url: 'setup',
      method: 'POST',
      data: formData,
      beforeSend: () => {
        $('#notification').html('Submitting credentials...')
        $('#btn-done').prop('disabled', true)
      },
      done: () => {
        $('#notification').html('')
        $('#btn-done').prop('disabled', false)
      },
      success: function(response) {
		return location.replace(location.href)
      },
      error: function(xhr, status, error) {
        return $('#notification').html('Failed to submit credentials!')
      }
    })
})
</script>