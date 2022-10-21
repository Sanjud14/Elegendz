<?php
require_once('includes/classes/User.php');

class UserGrid
{

    private $con, $userLoggedInObj;
    private $largeMode = false;
    private $gridClass = "user-grid container-fluid";

    public function __construct($con, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create($users, $title)
    {
        $header = "";
        if ($title != null) {
            $header .= $this->createGridHeader($title);
        }

        $gridItems = "";
        foreach ($users as $user) {
            $profileLink = "/" . $user->getUsername();
            $profilePicture = $user->getProfilePictureFullPath();
            $username = $user->getUsername();
            $gridItems .= "<div class='user-grid-item col-6 col-sm-6 col-md-4 mt-3 col-lg-3 col-xl-2 ps-sm-0 pe-sm-3'>
                                <a href='$profileLink'>
                                    <div class='thumbnail'>
                                        <img src='$profilePicture' class=''/>
                                    </div>
                                    <div class='details'>
                                    <h3>$username</h3>
                                    </div>
                                </a>
                            </div>";
        }

        return "$header
                <div class='$this->gridClass'>
                <div class='row'>
                    $gridItems
                    </div>
                </div>";
    }

    public function createGridHeader($title)
    {

        return "<div class='videoGridHeader container-fluid'>
                        <div class='row'>
                        <div class='col ps-0'>
                        <h3>
                            $title
                            </h3>
                        </div>
                        </div>
                    </div>";
    }
}