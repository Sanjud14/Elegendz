<?php
require_once("includes/header.php");
require_once("includes/classes/SearchResultsProvider.php");
require_once("includes/classes/NavigationMenuProvider.php");
require_once("includes/classes/UserGrid.php");

if (!isset($_GET["term"]) || $_GET["term"] == "") {
    echo "You must enter a search term";
    exit();
}

$term = $_GET["term"];

if (!isset($_GET["orderBy"]) || $_GET["orderBy"] == "views") {
    $orderBy = "views";
} else {
    $orderBy = "uploadDate";
}

$searchResultsProvider = new SearchResultsProvider($con, $userLoggedInObj);
$videos = $searchResultsProvider->getVideos($term, $orderBy);
$users = $searchResultsProvider->getUsers($term, 'username');

$videoGrid = new VideoGrid($con, $userLoggedInObj);
$userGrid = new UserGrid($con, $userLoggedInObj);

$navigationMenuProvider = new NavigationMenuProvider($con, $userLoggedInObj);
?>
    <div class="row">
        <div class="col-12 col-sm-12 col-lg-8 col-xl-8 offset-xl-2 offset-lg-2">
            <?php echo $navigationMenuProvider->createSearchBar($term, true); ?>
        </div>
    </div>
<?php if (sizeof($users) == 0 && sizeof($videos) == 0) { ?>
    <div class="row">
        <div class="col">
            <h3>
                No results found!
            </h3>
        </div>
    </div>
<?php } ?>
<?php if (sizeof($users) > 0) { ?>

    <?php echo $userGrid->create($users, sizeof($users) . " user(s) found") ?>

<?php } ?>
<?php if (sizeof($videos) > 0) { ?>
    <div class="largeVideoGridContainer">
        <?php
        if (sizeof($videos) > 0) {
            echo $videoGrid->createLarge($videos, sizeof($videos) . " video(s) found", true);
        } else {
            echo "No results found";
        }
        ?>
    </div>
<?php } ?>


<?php
require_once("includes/footer.php");
?>