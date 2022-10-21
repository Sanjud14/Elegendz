<?php
require_once("includes/classes/VideoInfoControls.php");

class VideoInfoSection
{

    private $con, $video, $userLoggedInObj;

    public function __construct($con, $video, $userLoggedInObj)
    {
        $this->con = $con;
        $this->video = $video;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create()
    {
        return $this->createPrimaryInfo() . $this->createSecondaryInfo();
    }

    private function createPrimaryInfo()
    {
        // $artist = $this->video->getArtistName();
        $title = $this->video->getTitle();
        $views = $this->video->getViews();
        //$monthlyViews = $this->video->getMonthlyViews();
        $monthlyLikes = $this->video->getMonthlyLikes();
        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $videoInfoControls = new VideoInfoControls($this->video, $this->userLoggedInObj);
        $controls = $videoInfoControls->create($currentUrl, $title);
        $category = $this->video->getCategoryName();

        return "<div class='videoInfo'>
                    <h1>$title</h1>
                    <div class='bottomSection'>
                    <div class='basic-info'>
                        <h5 class='category'>$category</h5>
                        <span class='viewCount'>$views views</span>
                    </div>
                        $controls
                    </div>
                </div>";
    }

    private function createSecondaryInfo()
    {

        // $description = $this->video->getDescription();
        $uploadDate = $this->video->getUploadDate();
        $uploadedBy = $this->video->getUploadedBy();
        $userFeatures = $this->video->getUsersFeatures();
        $producers = $this->video->getProducers();
        $profileButton = ButtonProvider::createUserProfileButton($this->con, $uploadedBy);

        if ($this->userLoggedInObj && $uploadedBy == $this->userLoggedInObj->getUsername()) {
            $actionButton = ButtonProvider::createEditVideoButtons($this->video->getId());
        } else {
            $userToObject = new User($this->con, $uploadedBy);
            $actionButton = ButtonProvider::createSubscriberButton($this->con, $userToObject, $this->userLoggedInObj);
        }

        $featureString = "";
        if (sizeof($userFeatures) > 0) {
            $featureString = "<span class='owner'> Featuring ";
            foreach ($userFeatures as $user) {
                $featureString .= "<a href='/" . $user->getUsername() . "'>" . $user->getUsername() . "</a>&nbsp;";
            }
            $featureString .= "</span>";
        }

        //same with producers
        $producersString = "";
        if (sizeof($producers) > 0) {
            $producersString = "<span class='owner'> Produced by ";
            foreach ($producers as $user) {
                $producersString .= "<a href='/" . $user->getUsername() . "'>" . $user->getUsername() . "</a> ";
            }
            $producersString .= "</span>";
        }

        $recordLabelHtml = "";
        if ($this->video->getRecordLabel()) {
            $user = new User($this->con, null, $this->video->getRecordLabel());
            $recordLabelHtml = "<span class='owner'>Record Label: <a href='/" . $user->getUsername() . "'>" . $user->getUsername() . "</a></span>";
        }

        $addToPlaylistHtml = "";
        if ($this->userLoggedInObj && $this->video->getAudioFilePath())
            $addToPlaylistHtml = "<div class='float-end' id='add_playlist_wrapper'>
                        <a href='javascript:void(0);' id='add_playlist' class='btn btn-outline-light btn-sm' onclick='addToPlaylist(" . $this->video->getId() . ")'>
                            <i class='bi bi-music-note-list'></i> ADD TO MY PLAYLIST
                            </a>
                        </div>";

        return "<div class='secondaryInfo'>
                    <div class='topRow'>
                        $profileButton
                        $addToPlaylistHtml
                        <div class='uploadInfo'>
                            <span class='owner'>
                                <a href='/$uploadedBy'>
                                    $uploadedBy
                                </a>
                            </span>
                            $featureString
                            $producersString
                            $recordLabelHtml
                            <span class='date'>Published on $uploadDate</span>
                        </div>
                        <div id='interaction_box'>
                        $actionButton
                        </div>
                    </div>
        
                </div>";
    }

}

?>