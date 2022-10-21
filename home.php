<?php require_once("includes/header.php"); ?>


<div class="videoSection">
    <?php

    $subscriptionsProvider = new SubscriptionsProvider($con, $userLoggedInObj);

    $subscriptionsVideos = [];
    if ($userLoggedInObj)
        $subscriptionsVideos = $subscriptionsProvider->getVideos(true);


    $videoGrid = new VideoGrid($con, $userLoggedInObj);

    $loggedInVideos = [];
    if (User::isLoggedIn() /*&& sizeof($subscriptionsVideos) > 0*/) {
       // $loggedInVideos =  $videoGrid->create($subscriptionsVideos, /*"Subscriptions & Categories You Prefer"*/null, false,true);
    }

    $standardVideos = $videoGrid->create(null, /*"Recommended"*/null, false, true);

    require_once ('_scrollable_video_grid.php');
    ?>

</div>


<?php require_once("includes/footer.php"); ?>
                