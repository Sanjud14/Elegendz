<?php

class SearchResultsProvider
{

    private $con, $userLoggedInObj;

    public function __construct($con, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function getVideos($term, $orderBy)
    {
        $term = str_replace(' ', '%', $term);
        $query = $this->con->prepare("SELECT * FROM videos WHERE title LIKE CONCAT('%', :term, '%')
                                        OR uploadedBy LIKE CONCAT('%', :term, '%') ORDER BY $orderBy DESC LIMIT 50");
        $query->bindParam(":term", $term);
        $query->execute();

        $videos = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $video = new Video($this->con, $row, $this->userLoggedInObj);
            array_push($videos, $video);
        }
        return $videos;
    }

    public function getUsers($term, $orderBy)
    {
        $term = str_replace(' ', '%', $term);
        $query = $this->con->prepare("SELECT * FROM users WHERE username LIKE CONCAT('%', :term, '%') ORDER BY $orderBy DESC LIMIT 2");
        $query->bindParam(":term", $term);
        $query->execute();

        $users = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row['username']);
            array_push($users, $user);
        }
        return $users;
    }

}

?>