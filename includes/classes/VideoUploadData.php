<?php

class VideoUploadData
{

    public $videoDataArray, $title, $privacy, $category, $uploadedBy;

    public function __construct($videoDataArray, $title, $privacy, $category, $uploadedBy)
    {
        $this->videoDataArray = $videoDataArray;
        $this->title = $title;
        //   $this->description = $description;
        $this->privacy = $privacy;
        $this->category = $category;
        $this->uploadedBy = $uploadedBy;
        //    $this->artistName = $artistName;
    }

    /**
     * Add featured users and producers extracted from string
     * @param $con PDO connection object
     * @param $videoId integer
     * @param $featuredUsers string
     * @param $producers string
     * @param $recordLabel string
     *
     */
    public static function AddFeaturedUsers($con, $videoId, $featuredUsers, $producers, $recordLabel)
    {

        $video = new Video($con, $videoId, null);
        $author = new User($con, $video->getUploadedBy());
        $featureTypes = [['table' => 'videos_features', 'input' => $featuredUsers, 'role' => 'featured'], ['table' => 'videos_producers', 'input' => $producers, 'role' => 'producer']];
        foreach ($featureTypes as $type) {

            //delete all previous associations
            $query = $con->prepare("DELETE FROM " . $type['table'] . " WHERE video_id=:videoId ");
            $query->bindParam(":videoId", $videoId);
            $query->execute();

            //parse string
            $inputParts = explode('@', $type['input']);
            foreach ($inputParts as $candidate) {
                $nickname = trim($candidate);
                //check if user exists
                $query = $con->prepare("SELECT id, email FROM users WHERE username = :username");
                $query->bindParam(":username", $profileUsername);
                $profileUsername = $nickname;
                $query->execute();
                $user = $query->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $query = $con->prepare("INSERT INTO " . $type['table'] . "( user_id,video_id) VALUES (:userId, :videoId)");
                    $query->bindParam(":userId", $user['id']);
                    $query->bindParam(":videoId", $videoId);
                    $query->execute();
                    //notify user

                    $title = "You've been tagged in <a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/watch?id=" . $videoId . "'>" . $video->getTitle() . "</a>.";
                    Notification::addNotification($con, "You have been tagged as " . $type['role'] . " in a new song!", $video->getThumbnail(), $author->getId(), $user['id'], $title, '/watch?id=' . $videoId);
                    Notification::sendEmailNotification("You have been tagged as " . $type['role'] . " in a new song!", $title, $user['email']);
                }
            }
        }
        //record label
        if (!empty($recordLabel)) {
            $nickname = trim(str_replace('@', ' ', $recordLabel));
            //check if user exists
            $query = $con->prepare("SELECT id FROM users WHERE username = :username");
            $query->bindParam(":username", $profileUsername);
            $profileUsername = $nickname;
            $query->execute();
            $user = $query->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $query = $con->prepare("UPDATE videos SET record_label = :recordLabel WHERE id = :videoId");
                $query->bindParam(":recordLabel", $user['id']);
                $query->bindParam(":videoId", $videoId);
                $query->execute();
                //notify user
                $title = "You've been tagged in <a href='" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/watch?id=" . $videoId . "'>" . $video->getTitle() . "</a>.";
                Notification::addNotification($con, "You have been tagged as record label in a new song!", $video->getThumbnail(), $author->getId(), $user['id'], $title, '/watch?id=' . $videoId);
                Notification::sendEmailNotification("You have been tagged as record label in a new song!", $title, $user['email']);
            }
        }

    }
    

}

?>