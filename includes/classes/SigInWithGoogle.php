<?php
require_once('SignWithGoogle.class.php');
require_once("../config.php");
if (isset($_GET['code'])) {
    $token = $gClient->fetchAccessTokenWithAuthCode($_GET['code']);
} else {
    header("Location: /sign-in");
    exit();
}

if (!isset($token['error'])) {
    $oAuth = new \Google_Service_Oauth2($gClient);
    $userData = $oAuth->userinfo_v2_me->get();

    //insert data
    $controller = new Controller;
    $controller->insertData(array(
        'email' => $userData['email'],
        'avatar' => $userData['picture'],
        'picture' => $userData['picture'],
        'familyName' => $userData['familyName'],
        'givenName' => $userData['givenName'],
    ));
} else {
    header("Location: /sign-in");
    exit();
}

?>