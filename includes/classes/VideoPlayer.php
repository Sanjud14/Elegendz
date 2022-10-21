<?php

class VideoPlayer
{

    private $video;

    public function __construct($video)
    {
        $this->video = $video;
    }

    public function create($autoPlay)
    {
        if ($autoPlay) {
            $autoPlay = "autoplay";
        } else {
            $autoPlay = "";
        }
        $filePath = $this->video->getFilePath();
        $youtubeId = $this->video->getYoutubeId();
        $soundCloudIframe = $this->video->getSoundCloudIframe();
        if ($filePath)
            return "<video class='videoPlayer' controls $autoPlay>
                    <source src='$filePath' type='video/mp4'>
                    Your browser does not support the video tag
                </video>";
        elseif ($youtubeId != null)
            return "<div class='videoPlayer text-center video-responsive '>
                    <iframe width='auto' height='400' src='https://www.youtube.com/embed/$youtubeId?autoplay=1&color=#320550&modestbranding=1' title='YouTube video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>
                    </div>";
        elseif ($soundCloudIframe != null) {
            return $soundCloudIframe;
        }
    }

}

?>