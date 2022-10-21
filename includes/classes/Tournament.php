<?php

class Tournament
{
    private $con, $sqlData, $userLoggedInObj;

    public function __construct($con, $input, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;

        if (is_array($input)) {
            $this->sqlData = $input;
        } else {
            $query = $this->con->prepare("SELECT * FROM tournaments WHERE id = :id");
            $query->bindParam(":id", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function getCategoryId()
    {
        return $this->sqlData["category_id"];
    }

    public function getPreliminaryRoundStart()
    {
        return $this->sqlData["preliminary_round_start"];
    }

    public function getEnd()
    {
        return $this->sqlData["end"];
    }

    /*public function getRegionId()
    {
        return $this->sqlData["region_id"];
    }*/

    /**
     * Get next current or future tournament
     * @param $con PDO object
     * @param $userLoggedInObj Object | null
     * @return Object
     */
    /*public static function getNextTournament($con, $userLoggedInObj)
    {
        $query = $con->prepare("SELECT * FROM tournaments where preliminary_round_end >= NOW() LIMIT 1");
        //  $query->bindParam(":id", $input);
        $query->execute();

        return new Tournament($con, $query->fetch(PDO::FETCH_ASSOC), $userLoggedInObj);//$query->fetch(PDO::FETCH_ASSOC);
    }*/

    /**
     * Displays time until preliminary round start or end, in readable format
     * @return string
     */
   /* public function timeUntilStart()
    {
        $now = new DateTime('now');


        $preliminaryRoundStart = new DateTime($this->sqlData['preliminary_round_start']);
        $preliminaryRoundEnd = new DateTime($this->sqlData['preliminary_round_end']);
        $description = "";
        if ($preliminaryRoundStart < $now && $now < $preliminaryRoundEnd) {
            $futureDate = $preliminaryRoundEnd;
            $description .= "The next tournament's preliminary round will end in ";
        }
        if ($preliminaryRoundStart > $now) {
            $futureDate = $preliminaryRoundStart;
            $description .= "The next tournament's preliminary round will start in ";
        }

        $diff = $now->diff($futureDate);
        if ($diff->invert)
            return null;
        $readableDiff = "";
        if ($diff->m > 0)
            $readableDiff = ($diff->m + 1) . " months";
        elseif ($diff->d > 0)
            $readableDiff = ($diff->d + 1) . " days";
        elseif ($diff->h > 0)
            $readableDiff .= ($diff->h + 1) . " hours";
        elseif ($diff->i > 0)
            $readableDiff .= ($diff->i + 1) . " minutes";
        //$timediff = Date::now()->timespan($date);
        $description .= $readableDiff . "!";
        return $description;
    }*/

    public function getCategoryName()
    {
        $query = $this->con->prepare("SELECT * FROM categories where id = :categoryId");
        $categoryId = $this->getCategoryId();
        $query->bindParam(":categoryId", $categoryId);
        $query->execute();
        $category = $query->fetch(PDO::FETCH_ASSOC);
        return $category['name'];
    }

  /*  public function getRegionName()
    {
        $query = $this->con->prepare("SELECT * FROM regions where id = :regionId");
        $regionId = $this->getRegionId();
        $query->bindParam(":regionId", $regionId);
        $query->execute();
        $region = $query->fetch(PDO::FETCH_ASSOC);
        return $region['name'];
    }*/

}