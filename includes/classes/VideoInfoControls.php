<?php
require_once("includes/classes/ButtonProvider.php");

class VideoInfoControls
{

    private $video, $userLoggedInObj;

    public function __construct($video, $userLoggedInObj)
    {
        $this->video = $video;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create($url, $title)
    {
        $userIP = (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR']);
        $likeButton = $this->createLikeButton($userIP);
        //  $dislikeButton = $this->createDislikeButton();
        $shareButton = $this->createShareButton($url, $title);

        return "<div class='controls'>
                    $shareButton
                    $likeButton
            <div id='message' class=''></div>
                </div>";
    }

    private function createLikeButton($userIp)
    {
        $text = "<i class='bi bi-hand-thumbs-up-fill'></i> <span id='likes'>" . $this->video->getMonthlyLikes() . "</span>";
        $videoId = $this->video->getId();
        $action = "likeVideo(this, $videoId)";
        $class = "likeButton";

        // $imageSrc = "";//"assets/images/icons/thumb-up.png";

        if ($this->video->wasLikedBy($userIp)) {
            $class = "share_button-active";
            //$imageSrc = "assets/images/icons/thumb-up-active.png";
        }

        return ButtonProvider::createButton($text, null, $action, $class);
    }

    /* private function createDislikeButton()
     {
         $text = $this->video->getDislikes();
         $videoId = $this->video->getId();
         $action = "dislikeVideo(this, $videoId)";
         $class = "dislikeButton";

         $imageSrc = "assets/images/icons/thumb-down.png";

         if ($this->video->wasDislikedBy()) {
             $imageSrc = "assets/images/icons/thumb-down-active.png";
         }

         return ButtonProvider::createButton($text, $imageSrc, $action, $class);
     }*/

    /**
     * Create share buttons' modal triggered by a single button
     * @param $url string destination
     * @param $title string description
     * @return string HTML code
     */
    private function createShareButton($url, $title)
    {
        $str = "'" . $url . "'";
        $html = '<button id="share_button" title="Share" onclick="copyVideoLinkToClipBoard(' . $str . ')">
                    &nbsp;<i class="bi bi-share-fill"></i>&nbsp;
                </button>';
        return $html;
    }
}

?>