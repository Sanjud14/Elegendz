<?php
require_once("includes/config.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/FormSanitizer.php");
$account = new Account($con);

if (isset($_POST["submitButton"])) {

    $username = FormSanitizer::sanitizeFormUsername($_POST["username"]);
    $password = FormSanitizer::sanitizeFormPassword($_POST["password"]);

    $wasSuccessful = $account->login($username, $password);

    if ($wasSuccessful) {
        $user = new User($con, $username);
        $_SESSION["userLoggedIn"] = $username;
        $_SESSION["userRole"] = $user->getRole();
        $_SESSION["zipcode"] = $user->getZipcode();
        if ($user->isAdmin()) {
            header("Location: /admin/index");
        } elseif ($user->isSponsor()) {
            header("Location: /sponsor/index");
        } else {
            if (isset($_SESSION["originatingpage"]))
                header("Location: http://" . $_SESSION["originatingpage"]);
            else
                header("Location: /index");
        }
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
    <title>Sign in / E Legendz</title>
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

<div class="container mt-2">
    <div class="row">
        <div class="col-12">
            <?php require_once('./includes/_message_display.php') ?>
        </div>
    </div>
</div>
<div class="signInContainer">

    <div class="column m-2">

        <div class="header"><h3>Sign In</h3><br>
            <img src="/elegendz/assets/images/icons/Trophyicon.png" title="logo" alt="Site logo">
        </div>

        <div class="loginForm">

            <form action="sign-in" method="POST">
                <?php echo $account->getError(Constants::$loginFailed); ?>
                <?php echo $account->getError(Constants::$emailAddressNotConfirmed); ?>
                <input type="text" name="username" placeholder="Username" value="<?php getInputValue('username'); ?>"
                       required autocomplete="off">
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" name="submitButton" value="SUBMIT">

                <button type="button" name="googleSignIn" onclick="window.location ='<?php echo $login_url ?>' "><img
                            src="/elegendzassets/images/google-plus.png"> Login with Google
                </button>


            </form>


        </div>

        <a class="signInMessage" href="/elegendz/sign-up">Need an account? Sign up here!</a><br/>
        <a class="signInMessage" href="/elegendz/forgot_password.php">Forgot your password? Click here</a>

    </div>

</div>


</body>
</html>