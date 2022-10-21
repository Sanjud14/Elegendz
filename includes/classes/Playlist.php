<?php

require_once("ButtonProvider.php");
require_once("CommentControls.php");

class Playlist
{
    private $con, $sqlData, $userLoggedInObj;

    public function __construct($con, $input, $userLoggedInObj)
    {
        if (!is_array($input)) {
            $query = $con->prepare("SELECT * FROM playlists where id=:id");
            $query->bindParam(":id", $input);
            $query->execute();

            $input = $query->fetch(PDO::FETCH_ASSOC);
        }

        $this->sqlData = $input;
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }
}
