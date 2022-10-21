<?php
require_once("includes/config.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/User.php");

$account = new Account($con);

$key = $_GET['key'];
$user = $account->verifyPasswordRecoveryKey($key);
if (!$user) {
    echo "Wrong key! Please verify that the URL is correct.";
    exit;
}

if (isset($_POST["submitButton"])) {

    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    if ($account->updatePassword(null, $password1, $password2, $user->getUsername())) {
        $user->resetPasswordRecoveryKey();
        $_SESSION['message_display'] = "Your password was updated. You can now proceed to login.";
        $_SESSION['message_display_type'] = "success";

        header("Location: signIn");
    } else {
        echo "There was an error updating the password!";
        exit;
    }
}

function getInputValue($name)
{
    if (isset($_POST[$name])) {
        echo $_POST[$name];
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password / E Legendz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
    <?php require('./includes/_favicon.php') ?>
</head>
<body>

<div class="signInContainer">

    <div class="column m-2">

        <div class="header"><h3>Generate New Password</h3><br>
        </div>

        <div class="loginForm">

            <form method="POST">

                <p>Enter your new password</p>
                <?php echo $account->getError(Constants::$emailNotFound); ?>
                <input type="password" name="password1" placeholder="Password"
                       value="<?php getInputValue('password1'); ?>"
                       required>
                <input type="password" name="password2" placeholder="Repeat password"
                       value="<?php getInputValue('password2'); ?>"
                       required>
                <input type="submit" name="submitButton" class="btn btn-primary" value="SUBMIT">

            </form>


        </div>

    </div>

</div>


</body>
</html>