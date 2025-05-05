<?php
include 'main.php';
// Error message variable
$error_msg = '';
// Success message variable
$success_msg = '';
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
    $stmt = $con->prepare('SELECT * FROM accounts WHERE reset_code = ?');
    $stmt->bind_param('s', $_GET['code']);
    $stmt->execute();
    $stmt->store_result();
    // Check if the account exists...
    if ($stmt->num_rows > 0) {
        $stmt->close();
		// Validate form data
        if (isset($_POST['npassword'], $_POST['cpassword'])) {
            if (strlen($_POST['npassword']) > 20 || strlen($_POST['npassword']) < 5) {
            	$error_msg = 'A jelszónak 5 és 20 karakter között kell lennie!';
            } else if ($_POST['npassword'] != $_POST['cpassword']) {
                $error_msg = 'A jelszavaknak egyezniük kell!';
            } else {
				// Hash the new password
				$password = password_hash($_POST['npassword'], PASSWORD_DEFAULT);
				// Update the password in the database
                $stmt = $con->prepare('UPDATE accounts SET password = ?, reset_code = "" WHERE reset_code = ?');
                $stmt->bind_param('ss', $password, $_GET['code']);
                $stmt->execute();
                $stmt->close();
				// Output success message
                $success_msg = 'A jelszavad sikeresen megváltozott! Most már <a href="index.php" class="form-link">bejelentkezhetsz</a>!';
            }
        }
    } else {
		// Coundn't find the account with that reset code
        exit('Hibás kódot adtál meg!');
    }
} else {
	// No code specified in the URL (GET request)
    exit('Nincs megadva kód!');
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Jelszó visszaállítása</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">

			<h1>Jelszó visszaállítása</h1>

			<form action="reset-password.php?code=<?=$_GET['code']?>" method="post" class="form" autocomplete="off">

				<label class="form-label" for="npassword">Új jelszó</label>
				<div class="form-group">
					<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
					<input class="form-input" type="password" name="npassword" placeholder="Új jelszó" id="npassword" autocomplete="new-password" required>
				</div>

				<label class="form-label" for="cpassword">Jelszó megerősítése</label>
				<div class="form-group mar-bot-5">
					<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
					<input class="form-input" type="password" name="cpassword" placeholder="Jelszó megerősítése" id="cpassword" autocomplete="new-password" required>
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

				<button class="btn blue" type="submit">Mentés</button>

			</form>

		</div>
	</body>
</html>