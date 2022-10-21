<?php 
require_once("includes/header.php"); 
require_once("includes/classes/VideoPlayer.php"); 
require_once("includes/classes/VideoInfoSection.php"); 

if(!isset($_GET["id"])) {
    echo "No url passed into page";
    exit();
}

$video = new Video($con, $_GET["id"], $userLoggedInObj);
$video->incrementViews();


?>
<script src="assets/js/videoPlayerActions.js"></script>


<div>
  
  <select>
      <option>North Round 2 of 5 of Hip Hop</option> 
      <option>South Round 2 of 5 of Hip Hop</option>
      <option>East Round 2 of 5 of Hip Hop</option> 
      <option>West Round 2 of 5 of Hip Hop</option>
    
  </select>

</div>



<table class="vsTimeline">
    
  <tr>
    <th><div class="vswatchLeftColumn">

<?php
    $videoPlayer = new VideoPlayer($video);
    echo $videoPlayer->create(true);

    $videoPlayer = new VideoInfoSection($con, $video, $userLoggedInObj);
    echo $videoPlayer->create();
?>


</div></th><br>
    <th> <div class="vsGif"><img src="assets/images/icons/VS.gif.gif"> </div></th><br>
    <th><div class="vswatchRightColumn" https://blog.flamingtext.com/blog/2021/11/08/flamingtext_com_1636345081_846981395.gif>

    <?php
    $videoPlayer = new VideoPlayer($video);
    echo $videoPlayer->create(true);

    $videoPlayer = new VideoInfoSection($con, $video, $userLoggedInObj);
    echo $videoPlayer->create();
?>

</div></th>
 </tr>
   <tr>
    <td><button class="voteButton" name="vote">Vote</button></td>
    <td></td>
    <td><button class="voteButton" name="vote">Vote</button></td>
  </tr>

</table>




<?php require_once("includes/footer.php"); ?>
                