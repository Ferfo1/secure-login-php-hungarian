<?php
include 'main.php';
// Success message variable
$success_msg = '';
// First we check if the email and code exists, these variables will appear as parameters in the URL
if (isset($_GET['code']) && !empty($_GET['code'])) {
	// Check if the account exists with the specified activation code
	$stmt = $con->prepare('SELECT * FROM accounts WHERE activation_code = ? AND activation_code != "activated" AND activation_code != "deactivated"');
	$stmt->bind_param('s', $_GET['code']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		// Account exists with the requested code.
		$stmt->close();
		// Update the activation code column to "activated" - this is how we can check if the user has activated their account
		$stmt = $con->prepare('UPDATE accounts SET activation_code = "activated" WHERE activation_code = ?');
		$stmt->bind_param('s', $_GET['code']);
		$stmt->execute();
		$stmt->close();
		// Output success message
		$success_msg = 'Your account is now activated! You can now <a href="index.php" class="form-link">Login</a>.';
	} else {
		// Account with the code specified does not exist
		exit('The account is already activated or doesn\'t exist!');
	}
} else {
	exit('No code was specified!');
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Activate Account</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">

			<h1>Activate Account</h1>

			<div class="form register-form">

				<div class="msg success">
					<?=$success_msg?>
				</div>

			</div>

		</div>
	</body>
</html>