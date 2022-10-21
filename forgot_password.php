<?php
require_once("includes/config.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/User.php");

$account = new Account($con);

if (isset($_POST["submitButton"])) {

    $email = $_POST['email'];
    $user = $account->findUserByEmail($email);

    if ($user) {

        $recoveryKey = $account->generatePasswordRecoveryKey($user);
        $createPasswordUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/create_password.php?key=" . $recoveryKey;

        $body = "Click <a href='$createPasswordUrl'>here</a> to create a new password.";

        $headers = "From: ELegendz <noreply@elegendz.net>" . "\r\n" . "Reply-To: noreply@elegendz.net" . "\r\n" . "X-Mailer: PHP/" . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $wasSuccessful = mail($email, '[ELEGENDZ] Password recovery', $body, $headers);

        if ($wasSuccessful) {

            $_SESSION['message_display'] = "A mail has been sent to $email with a link to create a new password. Please check your inbox or the spam folder.";
            $_SESSION['message_display_type'] = "warning";

            header("Location: message");
        }
    }
    /* else {

     }*/

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

        <div class="header"><h3>Forgot Password</h3><br>
        </div>

        <div class="loginForm">

            <form method="POST">

                <p>Please enter your e-mail to reset your password.</p>
                <?php echo $account->getError(Constants::$emailNotFound); ?>
                <input type="email" name="email" placeholder="E-mail address" value="<?php getInputValue('email'); ?>"
                       required>
                <input type="submit" name="submitButton" class="btn btn-primary" value="SUBMIT">

            </form>


        </div>

    </div>

</div>


</body>
</html>