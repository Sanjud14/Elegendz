<?php

class SubscriptionsProvider
{

    private $con, $userLoggedInObj;

    public function __construct($con, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    /**
     * @param boolean $returnAsArray Whether each video is returned as an array or object
     * @return array of Object or array
     * @throws Exception
     */
    public function getVideos($returnAsArray = false)
    {
        $videos = [];
        $subscriptions = $this->userLoggedInObj->getSubscriptions();
        $categoriesVideos = [];
        if (sizeof($subscriptions) > 0) {
            //get video from subscribed user
            $condition = "";
            $i = 0;
            while ($i < sizeof($subscriptions)) {
                if ($i == 0) {
                    $condition .= "WHERE uploadedBy=?";
                } else {
                    $condition .= " OR uploadedBy=?";
                }
                $i++;
            }

            $videoSql = "SELECT videos.*,thumbnails.filePath as thumbnail FROM videos LEFT JOIN thumbnails on thumbnails.videoId = videos.id $condition GROUP BY videos.id ORDER BY uploadDate DESC LIMIT 2";
            $videoQuery = $this->con->prepare($videoSql);
            $i = 1;

            foreach ($subscriptions as $sub) {
                $subUsername = $sub->getUsername();
                $videoQuery->bindValue($i, $subUsername);
                $i++;
            }

            $videoQuery->execute();
            while ($row = $videoQuery->fetch(PDO::FETCH_ASSOC)) {
                if ($returnAsArray) {
                    $video = $row;
                    //  array_push($videos, $video);
                    if (sizeof($videos) == 0) //add just the first one
                        $videos[$video['id']] = $video;
                } else {
                    $video = new Video($this->con, $row, $this->userLoggedInObj);
                    //  array_push($videos, $video);
                    if (sizeof($videos) == 0) //add just the first one
                        $videos[$video->getId()] = $video;
                }

                $categoriesVideos[] = $video;
            }
        }

        $categories = $this->userLoggedInObj->getCategories();
        if (sizeof($categories) > 0) {
            //get video from chosen categories
            $condition = "";
            $categoriesIds = [];
            foreach ($categories as $i => $category) {
                if ($i == 0) {
                    $condition .= "WHERE category=?";
                } else {
                    $condition .= " OR category=?";
                }
                $categoriesIds[] = $category['category_id'];
            }

            $videoSql = "SELECT videos.*,thumbnails.filePath as thumbnail FROM videos LEFT JOIN thumbnails on thumbnails.videoId = videos.id $condition GROUP BY videos.id ORDER BY uploadDate DESC LIMIT 1";
            $videoQuery = $this->con->prepare($videoSql);

            $videoQuery->execute($categoriesIds);
            if ($videoQuery->rowCount() > 0) {
                $row = $videoQuery->fetch(PDO::FETCH_ASSOC);
                if ($returnAsArray) {
                    $video = $row;
                    $videos[$video['id']] = $video;
                } else {
                    $video = new Video($this->con, $row, $this->userLoggedInObj);
                    $videos[$video->getId()] = $video;
                }
            }
        }

        if (sizeof($subscriptions) > 0) {
            //get a subscribed user's rival's song if possible
            $condition = "";
            $subscriptionsNames = [];
            foreach ($subscriptions as $i => $sub) {
                if ($i == 0) {
                    $condition .= "WHERE uploadedBy=?";
                } else {
                    $condition .= " OR uploadedBy=?";
                }
                $subscriptionsNames[] = $sub->getUsername();
            }

            $videoSql = "SELECT * FROM (SELECT videos.*,song_1,song_2,matches.id as match_id,thumbnails.filePath as thumbnail FROM videos INNER JOIN matches ON videos.id = matches.song_1 LEFT JOIN thumbnails on thumbnails.videoId = videos.id GROUP BY videos.id
            UNION SELECT videos.*,song_1,song_2,matches.id as match_id,thumbnails.filePath as thumbnail FROM videos INNER JOIN matches ON videos.id = matches.song_2 LEFT JOIN thumbnails on thumbnails.videoId = videos.id GROUP BY videos.id) as match_videos $condition
            ORDER BY match_videos.uploadDate,match_videos.match_id DESC LIMIT 1";
            $videoQuery = $this->con->prepare($videoSql);


            $videoQuery->execute($subscriptionsNames);
            if ($videoQuery->rowCount() > 0) {
                $song = $videoQuery->fetch(PDO::FETCH_ASSOC);
                //Determine if it's song 1 or 2... then get the other (rival's)
                if ($song['id'] == $song['song_1'])
                    $rivalSong = $song['song_2'];
                else
                    $rivalSong = $song['song_1'];

                $videoQuery = $this->con->prepare("SELECT videos.*,thumbnails.filePath as thumbnail FROM videos LEFT JOIN thumbnails on thumbnails.videoId = videos.id WHERE videos.id = $rivalSong GROUP BY videos.id");
                $videoQuery->execute();
                $row = $videoQuery->fetch(PDO::FETCH_ASSOC);
                if ($returnAsArray) {
                    $video = $row;
                    $videos[$video['id']] = $video;
                } else {
                    $video = new Video($this->con, $row, $this->userLoggedInObj);
                    $videos[$video->getId()] = $video;
                }
            }
        }
        if (sizeof($videos) < 3 && sizeof($categoriesVideos) > 1) {
            if ($returnAsArray)
                $videos[$categoriesVideos[1]['id']] = $categoriesVideos[1];
            else
                $videos[$categoriesVideos[1]->getId()] = $categoriesVideos[1];
        }

        return array_values($videos);


    }
}

?>