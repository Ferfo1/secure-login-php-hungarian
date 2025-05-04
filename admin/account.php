<?php
include 'main.php';
// Default account values
$account = [
    'username' => '',
    'password' => '',
    'email' => '',
    'activation_code' => 'activated',
    'remember_me_code' => null,
    'role' => 'Member',
    'approved' => 1,
    'registered' => date('Y-m-d\TH:i'),
    'last_seen' => date('Y-m-d\TH:i')
];
// If editing an account
if (isset($_GET['id'])) {
    // Get the account from the database
    $stmt = $con->prepare('SELECT username, password, email, activation_code, remember_me_code, role, registered, last_seen, approved FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $stmt->bind_result($account['username'], $account['password'], $account['email'], $account['activation_code'], $account['rememberme'], $account['role'], $account['registered'], $account['last_seen'], $account['approved']);
    $stmt->fetch();
    $stmt->close();
    // ID param exists, edit an existing account
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Check to see if username already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? AND username != ?');
        $stmt->bind_param('ss', $_POST['username'], $account['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Username already exists!';
        }
        // Check to see if email already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE email = ? AND email != ?');
        $stmt->bind_param('ss', $_POST['email'], $account['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Email already exists!';
        }
        // Update the account
        if (!isset($error_msg)) {
            $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $account['password'];
            $activation_code = $_POST['activation_status'] == 'activated' || $_POST['activation_status'] == 'deactivated' ? $_POST['activation_status'] : $_POST['activation_code'];
            $approved = isset($_POST['approved']) && $_POST['approved'] ? 1 : 0;
            $stmt = $con->prepare('UPDATE accounts SET username = ?, password = ?, email = ?, activation_code = ?, role = ?, registered = ?, last_seen = ?, approved = ? WHERE id = ?');
            $stmt->bind_param('sssssssii', $_POST['username'], $password, $_POST['email'], $activation_code, $_POST['role'], $_POST['registered'], $_POST['last_seen'], $approved, $_GET['id']);
            $stmt->execute();
            header('Location: accounts.php?success_msg=2');
            exit;
        } else {
            // Update the account variables
            $account = [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'email' => $_POST['email'],
                'activation_code' => $_POST['activation_code'],
                'role' => $_POST['role'],
                'approved' => isset($_POST['approved']) && $_POST['approved'] ? 1 : 0,
                'registered' => $_POST['registered'],
                'last_seen' => $_POST['last_seen']
            ];
        }
    }
    if (isset($_POST['delete'])) {
        // Redirect and delete the account
        header('Location: accounts.php?delete=' . $_GET['id']);
        exit;
    }
} else {
    // Create a new account
    $page = 'Create';
    if (isset($_POST['submit'])) {
        // Check to see if username already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE username = ?');
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Username already exists!';
        }
        // Check to see if email already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE email = ?');
        $stmt->bind_param('s', $_POST['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Email already exists!';
        }
        // Insert new account into the database
        if (!isset($error_msg)) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $activation_code = $_POST['activation_status'] == 'activated' || $_POST['activation_status'] == 'deactivated' ? $_POST['activation_status'] : $_POST['activation_code'];
            $approved = isset($_POST['approved']) && $_POST['approved'] ? 1 : 0;
            $stmt = $con->prepare('INSERT IGNORE INTO accounts (username,password,email,activation_code,role,registered,last_seen,approved) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->bind_param('sssssssi', $_POST['username'], $password, $_POST['email'], $activation_code, $_POST['role'], $_POST['registered'], $_POST['last_seen'], $approved);
            $stmt->execute();
            header('Location: accounts.php?success_msg=1');
            exit;
        } else {
            // Update the account variables
            $account = [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'email' => $_POST['email'],
                'activation_code' => $_POST['activation_code'],
                'role' => $_POST['role'],
                'approved' => isset($_POST['approved']) && $_POST['approved'] ? 1 : 0,
                'registered' => $_POST['registered'],
                'last_seen' => $_POST['last_seen']
            ];
        }
    }
}
?>
<?=template_admin_header($page . ' Account', 'accounts', 'manage')?>

<form method="post" enctype="multipart/form-data">

    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2><?=$page?> Account</h2>
        <div class="btns">
            <a href="accounts.php" class="btn alt mar-right-1">Cancel</a>
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="btn red mar-right-1" onclick="return confirm('Are you sure you want to delete this account?')">
            <?php endif; ?>
            <input type="submit" name="submit" value="Save" class="btn">
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
    <div class="mar-top-4">
        <div class="msg error">
            <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>
            <p><?=$error_msg?></p>
            <svg class="close" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
        </div>
    </div>
    <?php endif; ?>

    <div class="content-block">

        <div class="form responsive-width-100">

            <label for="username"><span class="required">*</span> Username</label>
            <input type="text" id="username" name="username" placeholder="Username" value="<?=$account['username']?>" required>

            <label for="password"><?=$page == 'Edit' ? 'New ' : '<span class="required">*</span> '?>Password</label>
            <input type="password" id="password" name="password" placeholder="<?=$page == 'Edit' ? 'New ' : ''?>Password" autocomplete="new-password" value=""<?=$page == 'Edit' ? '' : ' required'?>>

            <label for="email"><span class="required">*</span> Email</label>
            <input type="text" id="email" name="email" placeholder="Email" value="<?=$account['email']?>" required>

            <div class="group">
                <div class="item">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <?php foreach ($roles_list as $role): ?>
                        <option value="<?=$role?>"<?=$role==$account['role']?' selected':''?>><?=$role?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="item">
                    <label for="activation_status">Status</label>
                    <select id="activation_status" name="activation_status">
                        <option value="activated"<?=$account['activation_code']=='activated'?' selected':''?>>Activated</option>
                        <option value="deactivated"<?=$account['activation_code']=='deactivated'?' selected':''?>>Deactivated</option>
                        <option value="pending"<?=$account['activation_code']!='activated'&&$account['activation_code']!='deactivated'?' selected':''?>>Pending</option>
                    </select>
                </div>
            </div>

            <div class="activation_code" style="display:<?=$account['activation_code']=='activated'||$account['activation_code']=='deactivated'?' none':' block'?>">
                <label for="activation_code">Activation Code</label>
                <input type="text" id="activation_code" name="activation_code" placeholder="Activation Code" value="<?=$account['activation_code']?>" required>
            </div>

            <label for="approved">Approved</label>
            <label for="approved" class="switch">
                <input type="checkbox" id="approved" name="approved" class="switch" value="1"<?=$account['approved'] ? ' checked' : ''?>>
                <span class="slider round"></span>
            </label>

            <div class="group mar-top-1">
                <div class="item">
                    <label for="registered">Registered Date</label>
                    <input id="registered" type="datetime-local" name="registered" value="<?=date('Y-m-d\TH:i', strtotime($account['registered']))?>" required>
                </div>
                <div class="item">
                    <label for="last_seen">Last Seen Date</label>
                    <input id="last_seen" type="datetime-local" name="last_seen" value="<?=date('Y-m-d\TH:i', strtotime($account['last_seen']))?>" required>
                </div>
            </div>

        </div>

    </div>

</form>

<script>
document.getElementById('activation_status').addEventListener('change', function() {
    if (this.value == 'activated' || this.value == 'deactivated') {
        document.querySelector('.activation_code').style.display = 'none';
        document.querySelector('#activation_code').value = this.value;
    } else {
        document.querySelector('.activation_code').style.display = 'block';
        document.querySelector('#activation_code').value = '';
        document.querySelector('#activation_code').focus();
    }
});
</script>

<?=template_admin_footer()?>