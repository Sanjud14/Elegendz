<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/includes/classes/SponsorSubscription.php');

class Sponsor
{
    private $con, $sqlData, $userLoggedInObj;

    public function __construct($con, $input, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;

        if (is_array($input)) {
            $this->sqlData = $input;
        } else {
            $query = $this->con->prepare("SELECT * FROM sponsors WHERE id = :id");
            $query->bindParam(":id", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);

        }

    }

    public function getId()
    {
        return $this->sqlData["id"];
    }

    public function getName()
    {
        return $this->sqlData["name"];
    }

    public function getPhone()
    {
        return $this->sqlData["phone"];
    }

    public function getLogo()
    {
        return $this->sqlData["logo"];
    }

    public function getBusinessType()
    {
        return $this->sqlData["business_type"];
    }

    public function getBusinessPitch()
    {
        return $this->sqlData["business_pitch"];
    }

    public function getAddress()
    {
        return $this->sqlData["address"];
    }

    public function getBackgroundColor()
    {
        return $this->sqlData["background_color"];
    }

    public function getUrl()
    {
        return $this->sqlData["url"];
    }

    public function getFontColor()
    {
        return $this->sqlData["font_color"];
    }

    public function getZipcode()
    {
        return $this->sqlData["zipcode"];
    }

    /**
     * Returns an active subscription if there is one
     * @return SponsorSubscription | null
     */
    public function getCurrentlyActiveSubscription()
    {
        $query = $this->con->prepare("SELECT * FROM sponsors_subscriptions WHERE sponsor_id = :sponsorId AND good_until IS NOT NULL AND good_until >= NOW() ORDER BY id DESC LIMIT 1");
        $sponsorId = $this->getId();
        $query->bindParam(":sponsorId", $sponsorId);
        $query->execute();

        $subscription = $query->fetch(PDO::FETCH_ASSOC);
        if ($subscription)
            return new SponsorSubscription($this->con, $subscription['id'], $this->userLoggedInObj);
        else
            return null;
    }

    /**
     * Returns last  inactive subscription if there is one (used to load previous selections)
     * @return SponsorSubscription | null
     */
    public function getLastInactiveSubscription()
    {
        $query = $this->con->prepare("SELECT * FROM sponsors_subscriptions WHERE sponsor_id = :sponsorId AND (good_until IS NULL OR good_until <= NOW()) ORDER BY id DESC LIMIT 1");
        $sponsorId = $this->getId();
        $query->bindParam(":sponsorId", $sponsorId);
        $query->execute();

        $subscription = $query->fetch(PDO::FETCH_ASSOC);
        if ($subscription)
            return new SponsorSubscription($this->con, $subscription, $this->userLoggedInObj);
        else
            return null;
    }

    /**
     * Returns all sponsor's subscriptions as array of arrays
     * @return array
     */
    public function getAllSubscriptions()
    {
        $query = $this->con->prepare("SELECT * FROM sponsors_subscriptions WHERE sponsor_id = :sponsorId ORDER BY id DESC");
        $sponsorId = $this->getId();
        $query->bindParam(":sponsorId", $sponsorId);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Checks if zipcode is a valid USA zipcode
     * @param $zipcode string
     * @param $con PDO object
     * @return bool
     */
    public static function isZipcodeValid($zipcode, $con)
    {
        $query = $con->prepare("SELECT * FROM zipcodes WHERE zipcode = :zipcode");
        $query->bindParam(":zipcode", $zipcode);
        $query->execute();
        $occurence = $query->fetch(PDO::FETCH_ASSOC);
        return (bool)$occurence;
    }

    /**
     * Return zipcode database data as array
     * @return array | null
     */
    public function getZipcodeData()
    {
        $query = $this->con->prepare("SELECT * FROM zipcodes WHERE zipcode = :zipcode");
        $zipcode = $this->getZipcode();
        if (!$zipcode)
            return null;

        $query->bindParam(":zipcode", $zipcode);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);

    }

}
