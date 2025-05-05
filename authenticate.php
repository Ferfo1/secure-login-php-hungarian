<?php
include 'main.php';
// Now we check if the data from the login form was submitted, isset() will check if the data exists
if (!isset($_POST['username'], $_POST['password'])) {
	// Could not retrieve the captured data, output error
	exit('Error: Kérjük, töltsd ki a felhasználónév és jelszó mezőket!');
}
// Prepare our SQL query and find the account associated with the login details
// Preparing the SQL statement will prevent SQL injection
$stmt = $con->prepare('SELECT id, password, remember_me_code, activation_code, role, username, approved FROM accounts WHERE username = ?');
// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string and therefore we specify "s"
$stmt->bind_param('s', $_POST['username']);
$stmt->execute();
$stmt->store_result();
// Check if the account exists:
if ($stmt->num_rows > 0) {
	// Bind results
	$stmt->bind_result($id, $password, $remember_me_code, $activation_code, $role, $username, $approved);
	$stmt->fetch();
	$stmt->close();
	// Account exists... Verify the form password
	if (password_verify($_POST['password'], $password)) {
		// Check if the account is activated
		if (account_activation && $activation_code != 'activated') {
			// User has not activated their account, output the message
			echo 'Error: Kérjük, aktiváld a fiókodat a bejelentkezéshez! Kattints <a href="resend-activation.php" class="form-link">ide</a> az aktiváló e-mail újraküldéséhez.';
		} else if ($activation_code == 'deactivated') {
			// The account is deactivated
			echo 'Error: A fiókod deaktiválva lett!';
		} else if (account_approval && !$approved) {
			// The account is not approved
			echo 'Error: A fiókod még nincs jóváhagyva!';
		} else {
			// Verification success! User has loggedin!
			// Regenerate session ID to invalidate the old one (helps to prevent session fixation attacks)
			session_regenerate_id();
			// Declare the session variables, which will basically act like cookies, but will store the data on the server as opposed to the client
			$_SESSION['account_loggedin'] = TRUE;
			$_SESSION['account_name'] = $username;
			$_SESSION['account_id'] = $id;
			$_SESSION['account_role'] = $role;
			// IF the "remember me" checkbox is checked...
			if (isset($_POST['remember_me'])) {
				// Generate a hash that will be stored as a cookie and in the database. It will be used to identify the user.
				$cookie_hash = !empty($remember_me_code) ? $remember_me_code : password_hash($id . $username . secret_key, PASSWORD_DEFAULT);
				// The number of days the user will be remembered
				$days = 30;
				// Create the cookie
				setcookie('remember_me', $cookie_hash, (int)(time()+60*60*24*$days));
				// Update the "rememberme" field in the accounts table with the new hash
				$stmt = $con->prepare('UPDATE accounts SET remember_me_code = ? WHERE id = ?');
				$stmt->bind_param('si', $cookie_hash, $id);
				$stmt->execute();
				$stmt->close();
			}
			// Update last seen date
			$date = date('Y-m-d\TH:i:s');
			$stmt = $con->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
			$stmt->bind_param('si', $date, $id);
			$stmt->execute();
			$stmt->close();
			// Success! Redirect to the home page
			// Output msg: do not change this line as the AJAX code depends on it
			echo 'Redirect: home.php'; 
		}
	} else {
		// Incorrect password
		echo 'Error: Hibás felhasználónév és/vagy jelszó!';
	}
} else {
	// Incorrect username
	echo 'Error: Hibás felhasználónév és/vagy jelszó!';
}
?>