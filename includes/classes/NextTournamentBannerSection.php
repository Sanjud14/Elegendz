<?php

class NextTournamentBannerSection
{

    private $con, $tournament, $userLoggedInObj;

    public function __construct($con, $tournament, $userLoggedInObj)
    {
        $this->con = $con;
        $this->tournament = $tournament;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create($nextTournament)
    {
        return $this->createNextTournamentBannerSection($nextTournament);
    }

    private function createNextTournamentBannerSection($nextTournament)
    {

        return "<div id='next_tournament'>" . $nextTournament->timeUntilStart() . "</div>\n";
    }
}