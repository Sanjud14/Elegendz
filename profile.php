<?php
require_once("includes/config.php");

$user = new User($con, $profileUsername);

$pageTitle = $profileUsername;
define("OG_TITLE", $profileUsername);
define("OG_URL", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . $profileUsername);
const OG_TYPE = 'music.playlist';
define("OG_DESCRIPTION", $user->getTotalViews() . " total views");
define("OG_IMAGE", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $user->getProfilePictureFullPath());
// require_once("/elegendz/includes/header.php");
// require_once("/elegendz/includes/classes/ProfileGenerator.php");

require_once dirname(__FILE__) . '/includes/header.php';
require_once dirname(__FILE__) . '/includes/classes/ProfileGenerator.php';

/*if (isset($_GET["username"])) {
    $profileUsername = $_GET["username"];
} else {
    echo "Channel not found";
    exit();
}*/

$profileGenerator = new ProfileGenerator($con, $userLoggedInObj, $profileUsername);
echo $profileGenerator->create();
?>
<script type="text/javascript">
    window.onload = function () {
        let tab = window.location.hash.split('#')[1];
        if (tab) {
            let triggerEl = document.querySelector('.nav-item a[href="#' + tab + '"]').click();
        }
    }
</script>
<?php require_once("includes/footer.php"); ?>
