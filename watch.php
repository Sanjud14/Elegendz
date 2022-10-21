<?php
require_once("includes/config.php");
require_once("includes/classes/User.php");
require_once("includes/classes/Video.php");
require_once("includes/classes/VideoPlayer.php");
require_once("includes/classes/VideoInfoSection.php");
require_once("includes/classes/Comment.php");
require_once("includes/classes/CommentSection.php");
require_once("includes/classes/Tournament.php");
require_once("includes/classes/NextTournamentBannerSection.php");


if (!isset($_GET["id"])) {
    echo "Invalid page!";
    exit();
}
$usernameLoggedIn = User::isLoggedIn() ? $_SESSION["userLoggedIn"] : "";
$userLoggedInObj = User::isLoggedIn() ? new User($con, $usernameLoggedIn) : null;

$video = new Video($con, $_GET["id"], $userLoggedInObj);
$video->incrementViews();

define("OG_TITLE", $video->getTitle());
define("OG_URL", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/watch?id=' . $video->getId());
const OG_TYPE = 'music.song';
define("OG_DESCRIPTION", $video->getCategoryName() . ' - ' . $video->getDuration() . ' - ' . $video->getUploadedBy());
define("OG_IMAGE", $video->getThumbnail());

require_once("includes/header.php");

//print_r($_SERVER);
?>
<script src="assets/js/videoPlayerActions.js"></script>
<script src="assets/js/commentActions.js?v=002"></script>

<div class="watchLeftColumn">

    <?php
  /*  $nextTournament = Tournament::getNextTournament($con, $userLoggedInObj);
    if ($nextTournament) {
        $nextTournamentBanner = new NextTournamentBannerSection($con, $nextTournament, $userLoggedInObj);
        //   echo $nextTournamentBanner->create($nextTournament); temporarily disabled
    }*/

    $videoPlayer = new VideoPlayer($video);
    echo $videoPlayer->create(true);

    $videoPlayer = new VideoInfoSection($con, $video, $userLoggedInObj);
    echo $videoPlayer->create();

    $commentSection = new CommentSection($con, $video, $userLoggedInObj);
    echo $commentSection->create();
    ?>


</div>


<?php require_once("includes/footer.php"); ?>
                