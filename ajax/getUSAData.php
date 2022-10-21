<?php
require_once("../includes/config.php");

$action = $_GET['action'];

switch ($action) {
    case "get_state_counties":
        $state = $_GET['state'];
        if (!$state) {
            echo "Missing state!";
            exit;
        }
        $query = $con->prepare("SELECT DISTINCT( county) FROM zipcodes  WHERE state = :state ORDER BY county ASC ");
        $query->bindParam(":state", $state);
        $query->execute();
        $counties = $query->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['counties' => $counties]);
        break;
    case "get_county_cities":
        if (!isset($_GET['county']) || !isset($_GET['state'])) {
            echo "Missing county or state!";
            exit;
        }
        $county = $_GET['county'];
        $state = $_GET['state'];

        $query = $con->prepare("SELECT city AS name, COUNT(zipcode) AS zipcodes FROM zipcodes WHERE county = :county AND state = :state GROUP BY city ORDER BY city ASC");
        $query->bindParam(":county", $county);
        $query->bindParam(":state", $state);
        $query->execute();
        $cities = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['cities' => $cities]);
        break;
}

