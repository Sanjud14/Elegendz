<?php
require_once("includes/header.php");
require_once("includes/classes/VideoUploadData.php");
require_once("includes/classes/VideoProcessor.php");
require_once("includes/classes/User.php");

//requires logged in
if (!User::isLoggedIn())
    header("Location: /sign-in");
$username = $_SESSION["userLoggedIn"];
$user = new User($con, $username);

if (!isset($_POST["uploadButton"])) {
    echo "No song sent to page.";
    exit();
}
$wasSuccessful = false;
$videoId = null;

//prepare database as UTF8 with 4 bytes:
/*$query = $con->prepare("SET NAMES 'utf8mb4'");
$query->execute();
$query = $con->prepare("SET CHARACTER SET utf8mb4");
$query->execute();*/

switch ($_POST['type']) {

    case 'file':
        // 1) create file upload data
        $videoUploadData = new VideoUploadData(
            $_FILES["fileInput"],
            $_POST["titleInput"],
            /* $_POST["descriptionInput"],*/
            /* $_POST["privacyInput"]*/ 1,
            $_POST["categoryInput"],
            $userLoggedInObj->getUsername()
        );

        // 2) Process video data (upload)
        $videoProcessor = new VideoProcessor($con);
        $wasSuccessful = $videoProcessor->upload($videoUploadData, $videoId);
        break;
    case 'youtube':
        //youtube video

        //deal with duration
        $youtube_time = null;
        if (isset($_POST['youtube_duration']) && $_POST['youtube_duration'] != '') {
            $start = new DateTime('@0'); // Unix epoch
            $start->add(new DateInterval($_POST['youtube_duration']));
            $youtube_time = $start->format('i:s');
        }
        $query = $con->prepare("INSERT INTO videos(title, uploadedBy, privacy, category, youtube_id,duration)
                                        VALUES(:title, :uploadedBy, 1, :category, :youtubeId,:duration)");

        $username = $userLoggedInObj->getUsername();
        $query->bindParam(":title", $_POST["titleInput"]);
        $query->bindParam(":uploadedBy", $username);
        /*  $query->bindParam(":description", $_POST["descriptionInput"]);*/
        //  $query->bindParam(":privacy", 1);
        $query->bindParam(":category", $_POST["categoryInput"]);
        $query->bindParam(":youtubeId", $_POST["youtube_id"]);
        $query->bindParam(":duration", $youtube_time);
        /* $query->bindParam(":artistName", $_POST["artist_name"]);*/

        $wasSuccessful = $query->execute();

        if ($wasSuccessful) {
            //upload a thumbnail
            $videoId = $con->lastInsertId();

            $query = $con->prepare("INSERT INTO thumbnails(videoId, filePath, selected)
                                        VALUES(:videoId, :filePath, 1)");
            $filePath = "https://img.youtube.com/vi/" . $_POST["youtube_id"] . "/0.jpg";
            $query->bindParam(":videoId", $videoId);
            $query->bindParam(":filePath", $filePath);
            //   $query->bindParam(":selected", 1);

            $success = $query->execute();

            if (!$success) {
                echo "Error inserting thumbail\n";
                return false;
            }
        }

        break;
    case 'soundcloud':
        //SoundCloud track
        $query = $con->prepare("INSERT INTO videos(title, uploadedBy, privacy, category, soundcloud_iframe ,duration)
                                        VALUES(:title, :uploadedBy, 1, :category, :soundCloudIframe,NULL)");

        $username = $userLoggedInObj->getUsername();
        $query->bindParam(":title", $_POST["titleInput"]);
        $query->bindParam(":uploadedBy", $username);
        $query->bindParam(":category", $_POST["categoryInput"]);
        $query->bindParam(":soundCloudIframe", $_POST["soundcloud_iframe"]);

        $wasSuccessful = $query->execute();
        if ($wasSuccessful) {
            //upload a thumbnail
            $videoId = $con->lastInsertId();

            $query = $con->prepare("INSERT INTO thumbnails(videoId, filePath, selected)
                                        VALUES(:videoId, :filePath, 1)");
            $filePath = $_POST["soundcloud_thumbnail"];
            $query->bindParam(":videoId", $videoId);
            $query->bindParam(":filePath", $filePath);

            $success = $query->execute();

            if (!$success) {
                echo "Error inserting thumbnail\n";
                return false;
            }

        } else
            echo "Error uploading SoundCloud track!";
        break;

}

// 3) Check if upload was successful
if ($wasSuccessful) {

    VideoUploadData::AddFeaturedUsers($con, $videoId, $_POST['featured_users'], $_POST['producers'], $_POST['record_label']);
    //notify subscribers
    $videoId = $user->getLastUploadedVideo();
    $subscribers = $userLoggedInObj->getSubscribers();
    $song = new Video($con, $videoId, $userLoggedInObj);
    foreach ($subscribers as $subscriber) {
        $title = "<a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/" . $userLoggedInObj->getUsername() . "'>" . $userLoggedInObj->getUsername() . "</a> has uploaded a new song. Check <a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/watch?id=" . $videoId . "'>" . $song->getTitle() . "</a>.";
        $body = "New " . $song->getCategoryName() . " song from " . $userLoggedInObj->getUsername() . ".";
        Notification::addNotification($con, $body, $song->getThumbnail(), $userLoggedInObj->getId(), $subscriber['id'], $title, "/watch?id=" . $videoId);
        Notification::sendEmailNotification($body, $title, $subscriber['email']);
    }

    if (file_exists($_FILES['audio_file_path']['tmp_name']) && is_uploaded_file($_FILES['audio_file_path']['tmp_name'])) {
        //attempt song upload
        try {
            $targetDir = "uploads/audio/";

            $tempFilePath = $targetDir . uniqid() . str_replace("'", "", basename($_FILES['audio_file_path']['tmp_name']));
            $tempFilePath = str_replace(" ", "_", $tempFilePath);

            $path_parts = pathinfo($_FILES["audio_file_path"]["name"]);
            $extension = $path_parts['extension'];
            $finalFilePath = $targetDir . uniqid() . '.' . $extension;


            if (move_uploaded_file($_FILES['audio_file_path']['tmp_name'], $finalFilePath)) {
                $query = $con->prepare("UPDATE videos SET audio_file_path = :audioPath WHERE id = :id");

                $query->bindParam(":audioPath", $finalFilePath);
                $query->bindParam(":id", $videoId);

                $wasSuccessful = $query->execute();

            }
        } catch (Exception $e) {
            $_SESSION['message_display'] = 'Audio file could not be uploaded: ' . $e->getMessage();
            $_SESSION['message_display_type'] = 'danger';
        }

    }

    header("Location: /watch?id=" . $videoId . "&message_display=Song succesfully created&message_display_type=success");
}
?>