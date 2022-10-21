<?php

class NavigationMenuProvider
{

    private $con, $userLoggedInObj;

    public function __construct($con, $userLoggedInObj)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create($userNewNotifications = 0)
    {
        $searchBar = $this->createSearchBar();

        $menuHtml = "";
        if (User::isLoggedIn()) {
            if ($_SESSION["userRole"] == User::ROLE_ADMIN)
                $menuHtml .= $this->createNavItem("Admin", "", "/admin/index");
            if ($_SESSION["userRole"] == User::ROLE_SPONSOR)
                $menuHtml .= $this->createNavItem("Sponsor Panel", "", "/sponsor/index");
            $menuHtml .= $this->createNavItem("Upload", "", "/user/upload");
            //  $menuHtml .= $this->createNavItem("Notifications" . ($userNewNotifications > 0 ? " (" . $userNewNotifications . ")" : ""), "", "notifications.php", false);
        }
        // $menuHtml .= $this->createNavItem("Trending", "", "/trending");
        $menuHtml .= $this->createNavItem("Legendz", "", "/legendz");
        $menuHtml .= $this->createNavItem("EZ News", "", "/news");
        $menuHtml .= $this->createNavItem("About EZ", "", "/about");
        $menuHtml .= $this->createNavItem("Terms/Privacy", "", "/terms.php");
        if (User::isLoggedIn()) {

            //	$menuHtml .= $this->createNavItem("Settings", "assets/images/icons/settings.png", "settings.php");

            //   $menuHtml .= $this->createNavItem("Logout", "", "/logout");

        }

        // Create subscriptions section

        return "<div class='navigationItems'>
                   <br>
                    $searchBar
                    $menuHtml
                </div>";
    }

    private function createNavItem($text, $icon, $link, $isImageIcon = true)
    {
        if ($isImageIcon)
            $iconCode = "<img src='$icon'>";
        else
            $iconCode = $icon;
        return "<div class='navigationItem'>
                    <a href='$link'>
                        $iconCode
                        <span>$text</span>
                    </a>
                </div>";
    }

    public function createSearchBar($textSearch = null, $large = false)
    {
        if ($textSearch)
            $valueHtml = " value=\"$textSearch\"";
        else
            $valueHtml = "";
        return '     <div class="searchBarContainer pt-3 pb-3 text-center">
                        <form action="search" class="justify-content-center" method="GET">
                            <input type="text" class="searchBar ' . ($large ? 'large-bar' : '') . '" name="term" placeholder="Search..." ' . $valueHtml . ' autocomplete="off" />
                            <button class="searchButton ' . ($large ? 'large-button' : '') . '">
                                <img src="/assets/images/icons/search.png">
                            </button>
                        </form>
                    </div>
                ';
    }

}

?>