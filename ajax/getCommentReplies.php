<?php
require_once("../includes/config.php");
require_once("../includes/classes/Comment.php");
require_once("../includes/classes/User.php");

if (User::isLoggedIn()) {
    $username = $_SESSION["userLoggedIn"];
    $userLoggedInObj = new User($con, $username);
} else
    $userLoggedInObj = null;
$videoId = $_POST["videoId"];
$commentId = $_POST["commentId"];


$comment = new Comment($con, $commentId, $userLoggedInObj, $videoId);

echo $comment->getReplies();
?>