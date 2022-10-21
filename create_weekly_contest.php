<?php
require_once('includes/config.php');
require_once('includes/classes/Notification.php');
require_once('includes/classes/Video.php');
require_once('includes/classes/User.php');
require_once('includes/log.php');

if (!isset($_GET['key']) || $_GET['key'] != $cronjobKey) {
    echo "Missing or wrong key!";
    exit;
}

wh_log('Starting weekly contest');

//create tournament
$nextFriday = date_create("next thursday 23:59")->format('Y-m-d');
$nextSaturday = date_create("next friday 23:59")->format('Y-m-d');
$query = $con->prepare("INSERT INTO tournaments ( preliminary_round_start, end,  type) VALUES (:contestStart,:contestEnd,\"weekly\")");
$query->bindParam(":categoryId", $category['id']);
//  $query->bindParam(":regionId", $region['id']);
$query->bindParam(":contestStart", $nextFriday);
$query->bindParam(":contestEnd", $nextSaturday);
$query->execute();

//mail everyone


