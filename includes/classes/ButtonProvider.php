<?php

class ButtonProvider
{

    public static $signInFunction = "notSignedIn()";

    public static function createLink($link)
    {
        return $link;//User::isLoggedIn() ? $link : ButtonProvider::$signInFunction;
    }

    public static function createButton($text, $imageSrc, $action, $class)
    {

        $image = ($imageSrc == null) ? "" : "<img src='$imageSrc'>";

        $action = ButtonProvider::createLink($action);

        return "<button class='$class' onclick='$action'>
                    $image
                 <span class='text'>$text</span>
                </button>";
    }

    public static function createHyperlinkButton($text, $imageSrc, $href, $class, $confirmText = null)
    {

        $image = ($imageSrc == null) ? "" : "<img src='$imageSrc'>";
        if ($confirmText)
            $confirmHtml = "onclick=\"return confirm('$confirmText')\"";
        else
            $confirmHtml = "";

        return "
                    <a class='$class' href='$href' $confirmHtml class='nav-link dropdown-toggle'>
                        $image
                        <span class='text'>$text</span>
                    </a>
               ";
    }

    public static function createUserProfileButton($con, $username, $dropdownMenu = false)
    {
        $userObj = new User($con, $username);
        $profilePic = $userObj->getProfilePictureFullPath();
        $link = "/$username";

        if (!$dropdownMenu)
            return "<a href='$link'>
                    <img src='$profilePic' class='profilePicture'>
                </a>";
        return "<div class='dropdown  d-inline' id='profile_dropdown'>
                    <a href='$link' class='' data-bs-toggle='dropdown' aria-expanded='false'>
                        <img src='/elegendz/$profilePic' class='profilePicture'>
                   </a>
                   <ul class='dropdown-menu'>
                        <li><a href='/elegendz/$username' class='dropdown-item'>My Profile</a></li>
                        <li><a href='/elegendz/logout' class='dropdown-item'>Log Out</a></li>
                    </ul>
                </div>";
    }

    public static function createEditVideoButtons($videoId)
    {
        $href = "editVideo.php?videoId=$videoId";

        $editButton = ButtonProvider::createHyperlinkButton("EDIT SONG", null, $href, "button btn edit ");

        $href = "deleteVideo.php?videoId=$videoId";

        $deleteButton = ButtonProvider::createHyperlinkButton("DELETE SONG", null, $href, "button btn btn-danger ", "Are you sure you want to delete this song?");

        return "<div class='editVideoButtonContainer'>
                    $editButton
                    $deleteButton
                </div>";
    }


    public static function createSubscriberButton($con, $userToObj, $userLoggedInObj, $withEmailButton = false)
    {
        if (!$userLoggedInObj)
            return "";//no subscribe for not logged in user!
        $userTo = $userToObj->getUsername();
        $userLoggedIn = $userLoggedInObj->getUsername();

        $isSubscribedTo = $userLoggedInObj->isSubscribedTo($userTo);
        $buttonText = $isSubscribedTo ? "SUBSCRIBED" : "SUBSCRIBE";
        $buttonText .= " " . $userToObj->getSubscriberCount();

        $buttonClass = $isSubscribedTo ? "unsubscribe button btn btn-sm btn-warning mb-1 mb-sm-0" : "subscribe button btn btn-outline-warning mb-1 mb-sm-0";
        $action = "subscribe(\"$userTo\", \"$userLoggedIn\", this)";

        $button = ButtonProvider::createButton($buttonText, null, $action, $buttonClass);

        if ($withEmailButton)
            $emailbutton = "<a href='mailto:" . $userToObj->getEmail() . "' class='button btn btn-info btn-sm '>Send Mail</a>";
        else
            $emailbutton = "";

        return "<div class='subscribeButtonContainer'>
                    $button
                    $emailbutton
                </div>";
    }

    public static function createUserProfileNavigationButton($con, $username)
    {
        if (User::isLoggedIn()) {
            return ButtonProvider::createUserProfileButton($con, $username, true);
        } else {
            return "<a href='/elegendz/sign-in' class='d-inline-block mt-2 mt-md-3 '>
                        <span class='signInLink'>SIGN IN</span>
                    </a>";
        }
    }

}

?>