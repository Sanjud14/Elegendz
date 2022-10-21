<?php
require_once("includes/header.php");
require_once("includes/classes/VideoPlayer.php");
require_once("includes/classes/VideoDetailsFormProvider.php");
require_once("includes/classes/VideoUploadData.php");
require_once("includes/classes/SelectThumbnail.php");

if (!User::isLoggedIn()) {
    header("Location: singIn");
}

if (!isset($_GET["videoId"])) {
    echo "No video selected";
    exit();
}

$video = new Video($con, $_GET["videoId"], $userLoggedInObj);

if ($video->getUploadedBy() != $userLoggedInObj->getUsername()) {
    echo "Not your video";
    exit();
}

//check if video is part of a match
$query = $con->prepare("SELECT * FROM matches WHERE song_1 = :videoId OR song_2 = :videoId");
$videoId = $video->getId();
$query->bindParam(":videoId", $videoId);
$query->execute();

if ($query->rowCount() == 0) {

    if ($video->delete()) {
        $_SESSION['message_display'] = "The song was deleted";
        $_SESSION['message_display_type'] = "warning";
        header("Location: index");
    } else
        echo "There was an error deleting the video!";
} else {
    $_SESSION['message_display'] = "Can't delete a song that is part of a match";
    $_SESSION['message_display_type'] = "warning";
    header("Location: watch?id=" . $videoId);
}

