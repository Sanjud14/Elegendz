<?php
$pageTitle = "Champions";
define("OG_TITLE", "Champions");
define("OG_URL", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/legendz' );
const OG_TYPE = 'music.playlist';
define("OG_DESCRIPTION", "Past champions organized by period and category.");
define("OG_IMAGE", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] ."/assets/images/icons/menutrophy.png");
require_once("includes/config.php");
require_once("includes/header.php");

$query = $con->prepare("SELECT CONCAT(MONTHNAME(tournaments.preliminary_round_start),' ',YEAR(tournaments.preliminary_round_start)) AS period,tournaments.*,categories.name AS category_name, videos.title AS title,videos.id AS song_id,videos.uploadedBy,videos.duration,
       users.id AS user_id,users.username AS user_name, videos.id AS id, thumbnails.filePath as thumbnail FROM `tournaments` INNER JOIN categories ON tournaments.category_id = categories.id 
        INNER JOIN videos on tournaments.champion = videos.id INNER JOIN users ON videos.uploadedBy = users.username LEFT JOIN thumbnails ON thumbnails.videoId = videos.id WHERE champion IS NOT NULL AND type = 'monthly' GROUP BY tournaments.id ORDER BY preliminary_round_start DESC, category_id ASC;");

$query->execute();
$tournaments = $query->fetchAll(PDO::FETCH_GROUP);//group by period

?>
    <h1>Legendz</h1>
    <div class="accordion" id="accordionPanelsStayOpenExample">
        <?php
        $i = 0;
        foreach ($tournaments as $period => $periodTournaments) {
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-heading-<?php echo $i ?>">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#panelsStayOpen-collapse-<?php echo $i ?>" aria-expanded="true"
                            aria-controls="panelsStayOpen-collapse-<?php echo $i ?>">
                        <?php echo $period ?>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapse-<?php echo $i ?>"
                     class="accordion-collapse collapse <?php if ($i == 0) echo 'show';//first one is open ?>"
                     aria-labelledby="panelsStayOpen-heading-<?php echo $i ?>">
                    <div class="accordion-body">
                        <?php
                        $standardVideos = $periodTournaments;
                        $subscriptionsVideos = [];
                        $championsMode = true;
                        $videoGridIndex = $i;
                        require('_scrollable_video_grid.php') ?>
                    </div>
                </div>
            </div>

            <?php
            $previousPeriod = $period;
            $i++;
        }
        ?>
    </div>
<?php
require_once("includes/footer.php");
?>