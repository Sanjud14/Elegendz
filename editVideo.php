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

if (isset($_POST['updateButton'])) {
    $query = $con->prepare("UPDATE videos SET title=:title, privacy=1, category=:category WHERE id = :videoId");

    $username = $userLoggedInObj->getUsername();
    $videoId = $video->getId();
    $query->bindParam(":title", $_POST["titleInput"]);
    $query->bindParam(":category", $_POST["categoryInput"]);
    $query->bindParam(":videoId", $videoId);

    $wasSuccessful = $query->execute();

    if ($wasSuccessful) {
        VideoUploadData::AddFeaturedUsers($con, $videoId, $_POST['featured_users'], $_POST['producers'], $_POST['record_label']);

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
                    $previousfile = $video->getAudioFilePath();

                    $query = $con->prepare("UPDATE videos SET audio_file_path = :audioPath WHERE id = :id");

                    $query->bindParam(":audioPath", $finalFilePath);
                    $query->bindParam(":id", $videoId);

                    $wasAudioSuccessful = $query->execute();
                    if ($wasAudioSuccessful) {
                        //delete previous
                        @unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $previousfile);
                    }

                }
            } catch (Exception $e) {
                $_SESSION['message_display'] = 'Audio file could not be uploaded: ' . $e->getMessage();
                $_SESSION['message_display_type'] = 'danger';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            }

        }

        $_SESSION['message_display'] = "The song's details were updated";
        $_SESSION['message_display_type'] = "warning";
        header("Location: watch?id=" . $videoId);
    }
}

$videoDetailsFormProvider = new VideoDetailsFormProvider($con);

?>

<div class="window-box" id='upload_form'>
    <h3 class="mb-2">Edit Song's Details</h3>
    <div class="row">
        <div class=" col-12 col-sm-8 col-md-6 col-lg-4 offset-lg-4 offset-md-3 offset-sm-2">
            <?php
            $videoPlayer = new VideoPlayer($video);
            echo $videoPlayer->create(false);
            ?>
        </div>
    </div>
    <div class="row">
        <div class=" col">
            <?php echo $videoDetailsFormProvider->createEditForm($video->getId(), $userLoggedInObj) ?>
        </div>
    </div>


</div>

<script type="text/javascript">

    var users = [
        <?php
        $users = User::getAllUsers($con);
        foreach ($users as $user) {
            echo " {username: '" . $user['username'] . "', state: '" . $user['state'] . "'},\n";
        }
        ?>
    ];

    document.addEventListener("DOMContentLoaded", function (event) {
        $('#featured_users_input,#producers_input,#record_label_input').suggest('@', {
            data: users,
            map: function (user) {
                return {
                    value: user.username,
                    text: '<strong>' + user.username + '</strong> <small>' + user.state + '</small>',
                }
            }
        });
    });

    var uploadField = document.getElementById("audio_file_path");

    uploadField.onchange = function () {
        if (this.files[0].size > 10 * 1024 * 1024) {
            alert("Audio file is too big! (15 MB max)");
            this.value = "";
        }
        ;
    };


</script>