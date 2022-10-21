<?php
require_once("../includes/config.php");
require_once("../includes/classes/Video.php");
require_once("../includes/classes/User.php");

$username = isset($_SESSION["userLoggedIn"]) ? $_SESSION["userLoggedIn"] : null;
$videoId = $_POST["videoId"];

$userLoggedInObj = ($username ? new User($con, $username) : null);
$video = new Video($con, $videoId, $userLoggedInObj);
$userIP = (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR']);

$result = $video->addToPlaylist($userIP);