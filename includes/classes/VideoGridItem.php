<?php

class VideoGridItem
{

    private $video, $largeMode, $tournamentWon;

    public function __construct($video, $largeMode, $tournamentWon = null)
    {
        $this->video = $video;
        $this->largeMode = $largeMode;
        $this->tournamentWon = $tournamentWon;
    }

    public function create()
    {
        $thumbnail = $this->createThumbnail();
        $details = $this->createDetails();
        $url = "/watch?id=" . $this->video->getId();
        if ($this->tournamentWon) {
            //$tournamentWon = $this->video->getTrophy();
            $trophyHtml = "<i class='bi bi-trophy-fill'> </i>" . $this->tournamentWon->getCategoryName() /* ' - ' . $this->tournamentWon->getRegionName()*/
            ;
        } else
            $trophyHtml = "";

        return "  <div class='videoGridItem col-sm-6 col-md-6 mt-3 col-lg-4 col-xl-3 ps-sm-0'>
                    <a href='$url' class='song-link'>
                        $trophyHtml
                        $thumbnail
                        $details
                         </a>
                    </div>
               ";
    }

    private function createThumbnail()
    {

        $thumbnail = $this->video->getThumbnail();
        $duration = $this->video->getDuration();

        return "<div class='thumbnail'>
                    <img src='$thumbnail' class='img-fluid'>
                    <div class='duration'>
                        <span>$duration</span>
                    </div>
                </div>";

    }

    private function createDetails()
    {
        $title = $this->video->getTitle();
        $username = $this->video->getUploadedBy();
        $views = $this->video->getViews();
        //$monthlyViews = $this->video->getMonthlyViews();
        $monthlyLikes = $this->video->getMonthlyLikes();
        // $description = $this->createDescription();
        // $timestamp = date("M jS, Y", strtotime($this->video->getTimeStamp()));

        return "<div class='details'>
                    <h3 class='title'>$title</h3>
                    " . ($this->tournamentWon ? "<span>" . date("F Y", strtotime($this->tournamentWon->getEnd())) . " champion</span>" : "<span class='username'>$username</span>") . "
                    <div class='stats'>
                        <span class='viewCount'><i class='bi bi-hand-thumbs-up-fill'></i> $monthlyLikes likes this month</span> $views streams
                    </div>
                </div>";
    }

    /*   private function createDescription()
       {
           if (!$this->largeMode) {
               return "";
           } else {
               $description = $this->video->getDescription();
               $description = (strlen($description) > 350) ? substr($description, 0, 347) . "..." : $description;
               return "<span class='description'>$description</span>";
           }
       }*/

}

?>