<?php

class TrendingProvider
{

    private $con, $userLoggedInObj;

    public function __construct($con, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function getVideos($userLoggedInObj)
    {
        $videos = array();

        if ($userLoggedInObj) {
            $categories = $userLoggedInObj->getCategories();
            $categoriesConditions = "";
            if (sizeof($categories) > 0) {
                $categoriesConditions .= " AND (";
                foreach ($categories as $i => $category) {
                    $categoriesConditions .= ($i == 0 ? "" : " OR ") . "category = " . $category['category_id'];
                }
                $categoriesConditions .= ") ";
            }

        } else
            $categoriesConditions = "";
        $query = $this->con->prepare("SELECT videos.* FROM videos
                                        WHERE uploadDate >= now() - INTERVAL 200 DAY $categoriesConditions 
                                        GROUP BY videos.id
                                        ORDER BY monthly_likes DESC LIMIT 200");
        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $video = new Video($this->con, $row, $this->userLoggedInObj);
            array_push($videos, $video);
        }

        return $videos;
    }
}

?>