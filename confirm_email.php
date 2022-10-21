<?php
require_once("includes/config.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/User.php");

$key = $_GET['key'];
$id = $_GET['id'];

$account = new Account($con);
$user = new User($con, null, $id);

if (!$user) {
    echo "User not found! Please verify the URL is correct.";
    exit;
}
if ($user->getEmailValidationCode() == null) {
    $_SESSION['message_display'] = "Your email has already been confirmed.";
    $_SESSION['message_display_type'] = "success";

    header("Location: /sign-in");
}

$wasSuccessful = $account->verifyEmailConfirmationKey($user, $key);

if ($wasSuccessful) {
    $_SESSION['message_display'] = "Your email was succesfully validated. You can now proceed to login.";
    $_SESSION['message_display_type'] = "success";

    header("Location: /sign-in");
} else {
    echo "Wrong key! Please verify that the URL is correct!";
}