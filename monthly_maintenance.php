<?php
require_once('includes/config.php');
//require_once('includes/classes/Notification.php');
require_once('includes/classes/Video.php');
require_once('includes/classes/User.php');
require_once('includes/log.php');

if (!isset($_GET['key']) || $_GET['key'] != $cronjobKey) {
    echo "Missing or wrong key!";
    exit;
}

wh_log('Starting monthly script');

//categories
$query = $con->prepare("SELECT * FROM categories ORDER BY id ASC");
$query->execute();
$categories = $query->fetchAll();
//regions
$query = $con->prepare("SELECT * FROM regions ORDER BY id ASC");
$query->execute();
//$regions = $query->fetchAll();

$day = intval(date('d'));

$championsQty = 0;
//create tournaments & resolve
foreach ($categories as $category) {
    //determine winner
    $query = $con->prepare("SELECT videos.id, videos.title, users.id as creator_id, users.email as creator_email, videos.monthly_likes 
            FROM videos INNER JOIN users ON videos.uploadedBy = users.username
             WHERE category = :categoryId AND monthly_likes > 0 GROUP BY videos.id ORDER BY monthly_likes DESC, last_view ASC LIMIT 1");
    $query->bindParam(":categoryId", $category['id']);
    // $query->bindParam(":regionId", $region['id']);
    $query->execute();

    $champion = $query->fetch(PDO::FETCH_ASSOC);


//create tournament
    if ($champion)//create tournament if there is a champion only
    {
        $query = $con->prepare("INSERT INTO tournaments ( category_id, preliminary_round_start, end, champion, type, likes) VALUES ( :categoryId,:monthStart,:monthEnd,:champion,\"monthly\",:monthlyLikes)");
        $query->bindParam(":categoryId", $category['id']);
        //  $query->bindParam(":regionId", $region['id']);
        $lastMonthStart = date_create("first day of -1 month")->format('Y-m-d');
        $lastMonthEnd = date_create("last day of -1 month")->format('Y-m-d');
        $query->bindParam(":monthStart", $lastMonthStart);
        $query->bindParam(":monthEnd", $lastMonthEnd);
        $query->bindParam(":champion", $champion['id']);
        $query->bindParam(":monthlyLikes", $champion['monthly_likes']);
        $query->execute();
        $championsQty++;

        //notify winner
        $songCreator = new User($con, null, $champion['creator_id']);
        $song = new Video($con, $champion['id'], null);
        $thumbnail = $song->getThumbnail();
        $title = "<i class='bi bi-trophy-fill'></i> <a href='/watch?id=" . $champion['id'] . "'>" . $champion['title'] . "</a> is the newest E Legendz champion!";
        $body = "Congratulations! Your song is the " . $category['name'] . " champion for the period " . date_create("first day of -1 month")->format('F Y') . "!";
        Notification::addNotification($con, $body, "/assets/images/icons/trophy-small.png", null, $champion['creator_id'], $title, "/watch?id=" . $champion['id']);
       // Notification::sendEmailNotification($champion['title'] . " is the newest Elegendz monthly champion!", "Congratulations! <a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/watch?id=" . $champion['id'] . "'>" . $champion['title'] . "</a> is the " . $category['name'] . " champion for the period " . date_create("first day of -1 month")->format('F Y') . "!", $champion['creator_email']);
        //notify subscribers of that song's creator
        $subscribers = $songCreator->getSubscribers();
        foreach ($subscribers as $subscriber) {
            $title = "<a href='/watch?id=" . $champion['id'] . "'>" . $champion['title'] . "</a> from <a href='/" . $songCreator->getUsername() . "'>" . $songCreator->getUsername() . "</a> has won";
            $body = $champion['title'] . " was elected champion in the category " . $category['name'] . " for " . date_create("first day of -1 month")->format('F Y') . ".";
            Notification::addNotification($con, $body, $thumbnail, $songCreator->getId(), $subscriber['id'], $title, "/" . $songCreator->getUsername());
          //  Notification::sendEmailNotification($champion['title'] . " from " . $songCreator->getUsername() . " has won",
          //      "<a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/watch?id=" . $champion['id'] . "'>" . $champion['title'] . "</a> from <a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/" . $songCreator->getUsername() . "'>" . $songCreator->getUsername() . "</a> was elected champion in the category " . $category['name'] . " for " . date_create("first day of -1 month")->format('F Y') . ".", $subscriber['email']);
        }
    }
    // }
}

//reset monthly likes
$query = $con->prepare("UPDATE videos SET monthly_likes = 0 ");
$query->execute();

echo "$championsQty new champions!";
wh_log("$championsQty new champions were determined.");


/*if($day == intval(date('t')))
{
    //resolve tournament
}*/
