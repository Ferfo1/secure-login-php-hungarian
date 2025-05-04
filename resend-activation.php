<?php
include 'main.php';
// Error message variable
$error_msg = '';
// Success message variable
$success_msg = '';
// Now we check if the email from the resend activation form was submitted, isset() will check if the email exists.
if (isset($_POST['email'])) {
    // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
    $stmt = $con->prepare('SELECT activation_code FROM accounts WHERE email = ? AND activation_code != "" AND activation_code != "activated" AND activation_code != "deactivated"');
    // In this case we can use the account ID to get the account info.
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $stmt->store_result();
    // Check if the account exists:
    if ($stmt->num_rows > 0) {
        // account exists
        $stmt->bind_result($activation_code);
        $stmt->fetch();
        $stmt->close();
        // Send activation email
        send_activation_email($_POST['email'], $activation_code);
        // Output success message
        $success_msg = 'Activaton link has been sent to your email!';
    } else {
        $error_msg = 'An account with that email doesn\'t exist or is already activated!';
    }
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Resend Activation Email</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">

			<h1>Resend Activation Email</h1>

			<form action="resend-activation.php" method="post" class="form">

				<label class="form-label" for="email">Email</label>
				<div class="form-group mar-bot-5">
					<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
					<input class="form-input" type="email" name="email" placeholder="Email" id="email" required>
				</div>
				
				<?php if ($error_msg): ?>
				<div class="msg error">
					<?=$error_msg?>
				</div>
				<?php elseif ($success_msg): ?>
				<div class="msg success">
					<?=$success_msg?>
				</div>
				<?php endif; ?>

				<button class="btn blue" type="submit">Submit</button>

			</form>

		</div>
	</body>
</html>