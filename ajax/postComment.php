<?php
require_once("../includes/config.php");
require_once("../includes/classes/User.php");
require_once("../includes/classes/Comment.php");
require_once("../includes/classes/Video.php");
require_once("../includes/classes/Notification.php");

if (isset($_POST['commentText']) && isset($_POST['postedBy']) && isset($_POST['videoId'])) {

    $userLoggedInObj = new User($con, $_SESSION["userLoggedIn"]);

    $query = $con->prepare("SET NAMES 'utf8mb4'");
    $query->execute();
    $query = $con->prepare("SET CHARACTER SET utf8mb4");
    $query->execute();

    $query = $con->prepare("INSERT INTO comments(postedBy, videoId, responseTo, body)
                            VALUES(:postedBy, :videoId, :responseTo, :body)");
    $query->bindParam(":postedBy", $postedBy);
    $query->bindParam(":videoId", $videoId);
    $query->bindParam(":responseTo", $responseTo);
    $query->bindParam(":body", $commentText);

    $postedBy = $_POST['postedBy'];
    $videoId = $_POST['videoId'];
    $responseTo = ($_POST['responseTo'] ? $_POST['responseTo'] : 0);
    $commentText = $_POST['commentText'];

    $query->execute();

    $newComment = new Comment($con, $con->lastInsertId(), $userLoggedInObj, $videoId);
    echo $newComment->create();

    //add, send notification
    $video = new Video($con, $videoId, $userLoggedInObj);
    $videoCreator = new User($con, $video->getUploadedBy());
    if ($videoCreator->getId() != $userLoggedInObj->getId()) {//avoid notifying self comment
        $notificationTitle = "<a href=" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "\"/" . $userLoggedInObj->getUsername() . "\">" . $userLoggedInObj->getUsername() .
            "</a> has left a comment on your song <a href=" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "\"/watch?id=" . $video->getId() . "\">" . $video->getTitle() . "</a>";
        Notification::addNotification($con, $commentText, $video->getThumbnail(), $userLoggedInObj->getId(), $videoCreator->getId(), $notificationTitle, "/watch?id=" . $video->getId());
        Notification::sendEmailNotification("Someone commented on your song", $notificationTitle . ":<br/>" . $commentText, $videoCreator->getEmail());
    }
    //extra notification if the comment is a reply, to the original comment's author
    if ($_POST['responseTo']) {
        $originalComment = new Comment($con, $_POST['responseTo'], $userLoggedInObj, $videoId);
        $originalCommentAuthor = new User($con, $originalComment->getPostedBy());
        if ($originalCommentAuthor->getId() != $userLoggedInObj->getId()) {//don't notify if replying to themselves
            $notificationTitle = "<a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/" . $userLoggedInObj->getUsername() . "'>" . $userLoggedInObj->getUsername() . "</a> has left a comment in response to your comment";
            Notification::addNotification($con, $commentText, $video->getThumbnail(), $userLoggedInObj->getId(), $originalCommentAuthor->getId(), $notificationTitle, "/watch?id=" . $video->getId());
            Notification::sendEmailNotification("Someone responded your comment", $notificationTitle . ":<br/>" . $commentText, $originalCommentAuthor->getEmail());
        }
    }
} else {
    echo "One or more parameters are not passed into subscribe.php the file";
}

?>