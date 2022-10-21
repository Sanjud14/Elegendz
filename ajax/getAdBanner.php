<?php
require_once("../includes/config.php");
require_once("../includes/classes/Comment.php");
require_once("../includes/classes/Sponsor.php");
const DEFAULT_BANNER_ID = 4;

$zipcode = $_GET['zipcode'];
if (!$zipcode) {
    echo "Missing zipcode";
    exit;
}
if ($zipcode != 'NONE') {
    $query = $con->prepare("SELECT s.* FROM sponsors s INNER JOIN sponsors_subscriptions ss ON ss.sponsor_id = s.id
INNER JOIN sponsors_subscriptions_cities ssc ON ssc.subscription_id = ss.id INNER JOIN zipcodes z ON z.city = ssc.city AND z.county = ssc.county AND z.state = ssc.state
WHERE ss.good_until IS NOT NULL AND ss.good_until >= NOW()
AND z.zipcode = :zipcode ORDER BY RAND() LIMIT 1");
    $query->bindParam(":zipcode", $zipcode);
    $query->execute();
    $adSponsorArray = $query->fetch(PDO::FETCH_ASSOC);
    if ($adSponsorArray)
        $sponsor = new Sponsor($con, $adSponsorArray, null);
    else
        $sponsor = null;
}

if ($sponsor) {

} else {
    $query = $con->prepare("SELECT s.* FROM sponsors s  WHERE s.id = :id");
    $sponsorId = DEFAULT_BANNER_ID;
    $query->bindParam(":id", $sponsorId);
    $query->execute();
    $adSponsorArray = $query->fetch(PDO::FETCH_ASSOC);
    $sponsor = new Sponsor($con, $adSponsorArray, null);
}
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/_banner.php');
$bannerCode = ob_get_contents();
ob_end_clean();
echo json_encode(['banner_code' => $bannerCode, 'name' => $sponsor->getName()]);
//  echo json_encode([]);

