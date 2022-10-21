<?php

class ProfileData
{

    private $con, $profileUserObj;

    public function __construct($con, $profileUsername)
    {
        $this->con = $con;
        $this->profileUserObj = new User($con, $profileUsername);
    }

    public function getProfileUserObj()
    {
        return $this->profileUserObj;
    }

    public function getProfileUsername()
    {
        return $this->profileUserObj->getUsername();
    }

    public function userExists()
    {
        $query = $this->con->prepare("SELECT * FROM users WHERE username = :username");
        $query->bindParam(":username", $profileUsername);
        $profileUsername = $this->getProfileUsername();
        $query->execute();

        return $query->rowCount() != 0;
    }

    public function getCoverPhoto()
    {
        return "assets/images/coverPhotos/default-cover-photo.jpg";
    }

    public function getProfileUserFullName()
    {
        return $this->profileUserObj->getUsername();
    }

    public function getProfilePic()
    {
        //have to convert url to absolute instead of relative if it's not!
       /* $profilePicUrl = $this->profileUserObj->getProfilePic();
        if (substr($profilePicUrl, 0, 1) == '/')
            return $profilePicUrl;
        else
            return '/' . $profilePicUrl;*/
        return $this->profileUserObj->getProfilePictureFullPath();
    }

    public function getSubscriberCount()
    {
        return $this->profileUserObj->getSubscriberCount();
    }

//I was copying this code to make getUsersFeaturedVideos function
    public function getUsersVideos()
    {
        $query = $this->con->prepare("SELECT * FROM videos WHERE uploadedBy=:uploadedBy ORDER BY uploadDate DESC");
        $query->bindParam(":uploadedBy", $username);
        $username = $this->getProfileUsername();
        $query->execute();

        $videos = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $videos[] = new Video($this->con, $row, $this->profileUserObj->getUsername());
        }
        return $videos;
    }
//Need code below finished probably need to add column for features producers and record label in videos table
    // public function getUsersFeaturedVideos() {
    // $query = $this->con->prepare(SELECT * FROM videos_features WHERE user_id=:user_id );

    //   }

    public function getAllUserDetails()
    {
        return array("Username" => $this->getProfileUsername(),
            "Subscribers" => $this->getSubscriberCount(),
            "Total views" => $this->getTotalViews(),
            "Sign up date" => $this->getSignUpDate(),


        );
    }

    private function getTotalViews()
    {
        return "Test";
    }

    private function getSignUpDate()
    {
        return "Test 2";
    }

}

?>