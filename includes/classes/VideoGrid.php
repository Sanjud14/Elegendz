<?php
require_once('includes/classes/VideoGridItem.php');

class VideoGrid
{

    private $con, $userLoggedInObj;
    private $largeMode = false;
    private $gridClass = "videoGrid container";

    public function __construct($con, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function createLarge($videos, $title, $showFilter)
    {
        $this->gridClass .= " large";
        $this->largeMode = true;
        return $this->create($videos, $title, $showFilter);
    }

    public function create($videos, $title, $showFilter, $returnAsArray = false, $displayAsTrophies = false)
    {

        if ($videos === null) {
            $gridItems = $this->generateItems($returnAsArray);
        } else {
            $gridItems = $this->generateItemsFromVideos($videos, $returnAsArray, $displayAsTrophies);
        }

        if ($returnAsArray)
            return $gridItems;
        $header = "";

        if ($title != null) {
            $header = $this->createGridHeader($title, $showFilter);
        }

        return "$header
                <div class='$this->gridClass'>
                <div class='row'>
                    $gridItems
                    </div>
                </div>";
    }

    /**
     * @param boolean $returnAsArray Whether the function will return the HTML code or array of songs
     * @return string | array
     */
    public function generateItems($returnAsArray = false)
    {
        $songs = [];
        //   $songsTracking = [];//keep track to avoid repeated songs

        if ($this->userLoggedInObj) {
            $categories = $this->userLoggedInObj->getCategories();
            $categoriesConditions = "";
            if (sizeof($categories) > 0) {
                $categoriesConditions .= " WHERE (";
                foreach ($categories as $i => $category) {
                    $categoriesConditions .= ($i == 0 ? "" : " OR ") . "category = " . $category['category_id'];
                }
                $categoriesConditions .= ") ";
            }

        } else
            $categoriesConditions = "";
        $query = $this->con->prepare("SELECT videos.*,thumbnails.filePath as thumbnail, categories.name as category_name
        FROM videos LEFT JOIN thumbnails ON thumbnails.videoId = videos.id INNER JOIN users ON videos.uploadedBy = users.username
        INNER JOIN categories ON videos.category = categories.id $categoriesConditions
        WHERE monthly_views > 0 GROUP BY videos.id ORDER BY monthly_likes ASC LIMIT 200");
        $query->execute();
        $songs = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($returnAsArray)
            return $songs;

        $elementsHtml = "";
        foreach ($songs as $song) {
            $video = new Video($this->con, $song, ($this->userLoggedInObj ? $this->userLoggedInObj : null));
            $item = new VideoGridItem($video, $this->largeMode);
            $elementsHtml .= $item->create();
        }
        if (sizeof($songs) == 0)
            $elementsHtml = "<p>No songs of your categories found yet</p>";

        return $elementsHtml;
    }

    public function generateItemsFromVideos($videos, $returnAsArray = false, $displayAsTrophies = false)
    {
        $elementsHtml = "";
        if ($returnAsArray)
            $elementsArray = [];
        foreach ($videos as $video) {
            if (!is_object($video))
                $video = new Video($this->con, $video, ($this->userLoggedInObj ? $this->userLoggedInObj : null));
            $item = new VideoGridItem($video, $this->largeMode, ($displayAsTrophies ? $video->getTrophy() : null));
            if ($returnAsArray)
                $elementsArray[] = $item;
            else
                $elementsHtml .= $item->create();
        }
        if (!$returnAsArray && sizeof($videos) == 0)
            $elementsHtml = "<p>No songs found.</p>";
        if ($returnAsArray)
            return $elementsArray;
        else
            return $elementsHtml;
    }

    public function createGridHeader($title, $showFilter)
    {
        $filter = "";

        // create filter

        return "<div class='videoGridHeader container-fluid'>
                        <div class='row'>
                        <div class='col ps-0'>
                        <h3>
                            $title
                            </h3>
                        </div>
                        </div>
                        $filter
                    </div>";
    }

}

?>