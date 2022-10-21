<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/includes/classes/Notification.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/includes/classes/Tournament.php');

class Video
{

    private $con, $sqlData, $userLoggedInObj;

    public function __construct($con, $input, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;

        //prepare database as UTF8 with 4 bytes:
        /* $query = $con->prepare("SET NAMES 'utf8mb4'");
         $query->execute();
         $query = $con->prepare("SET CHARACTER SET utf8mb4");
         $query->execute();*/

        if (is_array($input)) {
            $this->sqlData = $input;
        } else {
            $query = $this->con->prepare("SELECT * FROM videos WHERE id = :id");
            $query->bindParam(":id", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function getId()
    {
        if (!$this->sqlData)
            throw new Exception("VIDEO NOT LOADED");
        return $this->sqlData["id"];
    }

    public function getUploadedBy()
    {
        return $this->sqlData["uploadedBy"];
    }

    public function getTitle()
    {
        return $this->sqlData["title"];
    }

    /* public function getDescription()
     {
         return $this->sqlData["description"];
     }*/

    /*  public function getPrivacy()
      {
          return $this->sqlData["privacy"];
      }*/

    public function getFilePath()
    {
        return $this->sqlData["filePath"];
    }

    public function getCategory()
    {
        return $this->sqlData["category"];
    }

    public function getCategoryName()
    {
        $query = $this->con->prepare("SELECT name from categories WHERE id=:categoryId");
        $category = $this->getCategory();
        $query->bindParam(":categoryId", $category);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        return $data["name"];
    }

    public function getUploadDate()
    {
        $date = $this->sqlData["uploadDate"];
        return date("M j, Y", strtotime($date));
    }

    public function getViews()
    {
        return $this->sqlData["views"];
    }

    public function getDuration()
    {
        return $this->sqlData["duration"];
    }

    public function getYoutubeId()
    {
        return $this->sqlData["youtube_id"];
    }

    public function getRecordLabel()
    {
        return $this->sqlData["record_label"];
    }

    public function getSoundCloudIframe()
    {
        return $this->sqlData["soundcloud_iframe"];
    }

    public function getAudioFilePath()
    {
        return $this->sqlData["audio_file_path"];
    }

    /*public function getMonthlyViews()
    {
        return $this->sqlData["monthly_views"];
    }*/

    public function getMonthlyLikes()
    {
        return $this->sqlData["monthly_likes"];
        /* $query = $this->con->prepare("SELECT count(*) FROM monthly_song_likes WHERE song_id = :songId");
         $query->bindParam(":songId", $id);
         $id = $this->sqlData["id"];
         $query->execute();

         return $query->fetchColumn();*/
    }

    public function incrementViews()
    {
        $query = $this->con->prepare("UPDATE videos SET views=views+1, monthly_views = monthly_views + 1 WHERE id=:id");
        $query->bindParam(":id", $videoId);

        $videoId = $this->getId();
        $query->execute();

        $this->sqlData["views"] = $this->sqlData["views"] + 1;
        $this->sqlData["monthly_views"] = $this->sqlData["monthly_views"] + 1;
    }

    /* public function getLikes()
     {
         $query = $this->con->prepare("SELECT count(*) as 'count' FROM likes WHERE videoId = :videoId");
         $query->bindParam(":videoId", $videoId);
         $videoId = $this->getId();
         $query->execute();

         $data = $query->fetch(PDO::FETCH_ASSOC);
         return $data["count"];
     }*/

    /*  public function getMonthlyLikes()
      {
          $query = $this->con->prepare("SELECT count(*) as 'count' FROM monthly_song_likes WHERE song_id = :videoId");
          $query->bindParam(":videoId", $videoId);
          $videoId = $this->getId();
          $query->execute();

          $data = $query->fetch(PDO::FETCH_ASSOC);
          return $data["count"];
      }*/

    /*public function getDislikes()
    {
        $query = $this->con->prepare("SELECT count(*) as 'count' FROM dislikes WHERE videoId = :videoId");
        $query->bindParam(":videoId", $videoId);
        $videoId = $this->getId();
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);
        return $data["count"];
    }*/

    public function like($userIP)
    {
        $id = $this->getId();
        $user = $this->userLoggedInObj ? $this->userLoggedInObj : null;
        if ($user) {
            $userId = $user->getId();
            $username = $user->getUsername();
        } else {
            $username = null;
            $userId = null;
        }

        if ($this->wasLikedBy($userIP)) {
            // User has already liked
            if ($user) {
                $query = $this->con->prepare("DELETE FROM song_likes WHERE  user_id = :userId AND song_id=:songId");
                $query->bindParam(":songId", $id);
                $query->bindParam(":userId", $userId);
                $query->execute();
            }
            //also deleted by IP
            $query = $this->con->prepare("DELETE FROM song_likes WHERE  ip = :ip AND song_id=:songId");
            $query->bindParam(":songId", $id);
            $query->bindParam(":ip", $userIP);
            $query->execute();

            $query = $this->con->prepare("UPDATE videos SET likes=GREATEST((likes-1),0),monthly_likes=GREATEST((monthly_likes-1),0) WHERE id=:videoId");
            $query->bindParam(":videoId", $id);
            $query->execute();

            //update cookie
            if (isset($_COOKIE['songs_liked'])) {
                $songsLiked = json_decode($_COOKIE['songs_liked'], true);
                unset($songsLiked[$id]);
                setcookie('songs_liked', json_encode($songsLiked), time() + (60 * 60 * 30 * 12), '/');
            }

            $result = array(
                "likes" => -1,
                // "dislikes" => 0
            );
            return json_encode($result);
        } else {
            /*  $query = $this->con->prepare("DELETE FROM dislikes WHERE username=:username AND videoId=:videoId");
              $query->bindParam(":username", $username);
              $query->bindParam(":videoId", $id);
              $query->execute();
              $count = $query->rowCount();*/

            if ($user) {
                $query = $this->con->prepare("INSERT INTO likes(username, videoId) VALUES(:username, :videoId)");
                $query->bindParam(":username", $username);
                $query->bindParam(":videoId", $id);
                $query->execute();
            }

            $query = $this->con->prepare("INSERT INTO song_likes (user_id, song_id, ip) VALUES(:userId, :videoId, :ip)");
            $query->bindParam(":userId", $userId);
            $query->bindParam(":videoId", $id);
            $query->bindParam(":ip", $userIP);
            $query->execute();

            $query = $this->con->prepare("UPDATE videos SET likes=(likes+1), monthly_likes=(monthly_likes+1) WHERE id=:videoId");
            $query->bindParam(":videoId", $id);
            $query->execute();

            //update cookie
            if (isset($_COOKIE['songs_liked']))
                $songsLiked = json_decode($_COOKIE['songs_liked'], true);
            else
                $songsLiked = [];
            $songsLiked[$id] = 1;
            setcookie('songs_liked', json_encode($songsLiked), time() + (60 * 60 * 30 * 12), '/');


            //notify creator
            $creator = new User($this->con, $this->getUploadedBy());
            if (($creator->getId() != $userId) && $user != null) {
                $title = "<a href='/" . $this->userLoggedInObj->getUsername() . "'>" . $this->userLoggedInObj->getUsername() . "</a> liked your song <a href='/watch?id=" . $this->getId() . "'>" . $this->getTitle() . "</a>!";
                Notification::addNotification($this->con, "", $this->getThumbnail(), $this->userLoggedInObj->getId(), $creator->getId(), $title, "/watch?id=" . $this->getId());
            }

            $result = array(
                "likes" => 1,
                //   "dislikes" => 0 - $count
            );
            return json_encode($result);
        }
    }

    /*  public function dislike()
      {
          $id = $this->getId();
          $username = $this->userLoggedInObj->getUsername();

          if ($this->wasDislikedBy()) {
              // User has already liked
              $query = $this->con->prepare("DELETE FROM dislikes WHERE username=:username AND videoId=:videoId");
              $query->bindParam(":username", $username);
              $query->bindParam(":videoId", $id);
              $query->execute();

              $result = array(
                  "likes" => 0,
                  "dislikes" => -1
              );
              return json_encode($result);
          } else {
              $query = $this->con->prepare("DELETE FROM likes WHERE username=:username AND videoId=:videoId");
              $query->bindParam(":username", $username);
              $query->bindParam(":videoId", $id);
              $query->execute();
              $count = $query->rowCount();

              $query = $this->con->prepare("INSERT INTO dislikes(username, videoId) VALUES(:username, :videoId)");
              $query->bindParam(":username", $username);
              $query->bindParam(":videoId", $id);
              $query->execute();

              $result = array(
                  "likes" => 0 - $count,
                  "dislikes" => 1
              );
              return json_encode($result);
          }
      }*/

    public function wasLikedBy($userIP)
    {
        /* if (!$this->userLoggedInObj)
             return false;*/
        $userId = $this->userLoggedInObj ? $this->userLoggedInObj->getId() : null;
        $id = $this->getId();
        //first try user id
        if ($userId) {
            $query = $this->con->prepare("SELECT * FROM song_likes WHERE user_id=:userId AND song_id=:songId");
            $query->bindParam(":userId", $userId);
            $query->bindParam(":songId", $id);
            $query->execute();
            if ($query->rowCount() > 0) {
                //   echo "user found!";
                return true;
            }
        }
        //try cookie
        if (isset($_COOKIE['songs_liked'])) {
            $songsLiked = json_decode($_COOKIE['songs_liked'], true);
            if (isset($songsLiked[$id])) {
                //    echo "cookie found!";
                return true;
            }
        }
        //try IP
        //   echo "SELECT * FROM song_likes WHERE ip=:$userIP AND song_id=:$id";
        $query = $this->con->prepare("SELECT * FROM song_likes WHERE ip=:ip AND song_id=:songId");
        $query->bindParam(":ip", $userIP);
        $query->bindParam(":songId", $id);
        $query->execute();
        if ($query->rowCount() > 0) {
            // echo "ip found!" . $query->rowCount();
            return true;
        }

        return false;
        //  $id = $this->getId();

        // $username = $this->userLoggedInObj->getUsername(); ??

    }

    /*  public function wasDislikedBy()
      {
          $query = $this->con->prepare("SELECT * FROM dislikes WHERE username=:username AND videoId=:videoId");
          $query->bindParam(":username", $username);
          $query->bindParam(":videoId", $id);

          $id = $this->getId();

          //    $username = $this->userLoggedInObj->getUsername(); ??
          $query->execute();

          return $query->rowCount() > 0;
      }*/

    public function getNumberOfComments()
    {
        $query = $this->con->prepare("SELECT * FROM comments WHERE videoId=:videoId AND deleted = 0");
        $query->bindParam(":videoId", $id);

        $id = $this->getId();

        $query->execute();

        return $query->rowCount();
    }

    public function getComments()
    {
        $query = $this->con->prepare("SELECT * FROM comments WHERE videoId=:videoId AND responseTo=0 AND deleted = 0 ORDER BY datePosted DESC");
        $query->bindParam(":videoId", $id);

        $id = $this->getId();

        $query->execute();

        $comments = array();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $comment = new Comment($this->con, $row, $this->userLoggedInObj, $id);
            array_push($comments, $comment);
        }

        return $comments;
    }

    public function getThumbnail()
    {
        $id = $this->getId();
        $query = $this->con->prepare("SELECT filePath FROM thumbnails WHERE videoid=:videoId LIMIT 1");
        $query->bindParam(":videoId", $id);
        $query->execute();
        $path = $query->fetchColumn();
        if ($path == null)
            return "/assets/images/icons/Trophyicon.png";
        return $path;
    }

    public function getTimeStamp()
    {
        return $this->sqlData["uploadDate"];
    }

    /**
     * Returns users featured in video as array of User objects
     * @return array
     * @throws Exception
     */
    public function getUsersFeatures()
    {
        $id = $this->getId();
        $query = $this->con->prepare("SELECT users.* FROM videos_features LEFT JOIN users ON videos_features.user_id = users.id WHERE video_id=:videoId ORDER BY id ASC");
        $query->bindParam(":videoId", $id);

        $query->execute();

        $users = array();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row['username']);
            array_push($users, $user);
        }
        return $users;
    }

    /**
     * Returns producers from video as array of User objects
     * @return array
     * @throws Exception
     */
    public function getProducers()
    {
        $id = $this->getId();
        $query = $this->con->prepare("SELECT users.* FROM videos_producers LEFT JOIN users ON videos_producers.user_id = users.id WHERE video_id=:videoId ORDER BY id ASC");
        $query->bindParam(":videoId", $id);

        $query->execute();

        $users = array();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row['username']);
            array_push($users, $user);
        }
        return $users;
    }

    /** Returns all videos the user is featured in
     * @param $con PDO connection object
     * @param $userId integer
     * @param $userLoggedIn User object | null
     * @return array
     */
    public static function getFeaturedVideos($con, $userId, $userLoggedIn)
    {
        $query = $con->prepare("SELECT videos.* FROM videos LEFT JOIN videos_features ON videos_features.video_id = videos.id  WHERE videos_features.user_id = :userId ORDER BY uploadDate DESC");
        $query->bindParam(":userId", $userId);
        $query->execute();

        $videos = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $videos[] = new Video($con, $row, $userLoggedIn ? $userLoggedIn->getUsername() : null);
        }
        return $videos;
    }

    /** Returns all videos produced by the user
     * @param $con PDO connection object
     * @param $userId integer
     * @param $userLoggedIn User object | null
     * @return array
     */
    public static function getProducedVideos($con, $userId, $userLoggedIn)
    {
        $query = $con->prepare("SELECT videos.* FROM videos LEFT JOIN videos_producers ON videos_producers.video_id = videos.id  WHERE videos_producers.user_id = :userId ORDER BY uploadDate DESC");
        $query->bindParam(":userId", $userId);
        $query->execute();

        $videos = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $videos[] = new Video($con, $row, $userLoggedIn ? $userLoggedIn->getUsername() : null);
        }
        return $videos;
    }

    /** Returns all videos where the user is marked as record label
     * @param $con PDO connection object
     * @param $userId integer
     * @param $userLoggedIn User object | null
     * @return array
     */
    public static function getRecordLabelVideos($con, $userId, $userLoggedIn)
    {
        $query = $con->prepare("SELECT videos.* FROM videos WHERE videos.record_label = :userId ORDER BY uploadDate DESC");
        $query->bindParam(":userId", $userId);
        $query->execute();

        $videos = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $videos[] = new Video($con, $row, $userLoggedIn ? $userLoggedIn->getUsername() : null);
        }
        return $videos;
    }

    /** Returns all videos where the user is marked as champion
     * @param $con PDO connection object
     * @param $userId integer
     * @param $userLoggedIn User object | null
     * @return array
     */
    public static function getUserChampionVideos($con, $userId, $userLoggedIn)
    {
        $query = $con->prepare("SELECT videos.* FROM videos INNER JOIN users ON users.username = videos.uploadedBy INNER JOIN tournaments ON tournaments.champion = videos.id WHERE users.id = :userId ORDER BY uploadDate DESC");
        $query->bindParam(":userId", $userId);
        $query->execute();

        $videos = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $videos[] = new Video($con, $row, $userLoggedIn ? $userLoggedIn->getUsername() : null);
        }
        return $videos;
    }

    /**
     * Delete current video
     * @return boolean
     * @throws Exception
     */
    public function delete()
    {
        $query = $this->con->prepare("DELETE FROM videos WHERE id=:videoId ");
        $videoId = $this->getId();
        $query->bindParam(":videoId", $videoId);
        return $query->execute();
    }

    /**
     * Retrieves tournament where a song won, or null if it didn't happen
     * @return Tournament object
     * @throws Exception
     */
    public function getTrophy()
    {
        $query = $this->con->prepare("SELECT * FROM tournaments where champion = :songId LIMIT 1");
        $songId = $this->getId();
        $query->bindParam(":songId", $songId);
        $query->execute();

        $tournament = $query->fetch(PDO::FETCH_ASSOC);

        if ($tournament)
            return new Tournament($this->con, $tournament, $this->userLoggedInObj);
        else
            return null;
    }

    public function addToPlaylist()
    {

    }

}

