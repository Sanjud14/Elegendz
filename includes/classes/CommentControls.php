<?php
require_once("ButtonProvider.php");

class CommentControls
{

    private $con, $comment, $userLoggedInObj;

    public function __construct($con, $comment, $userLoggedInObj)
    {
        $this->con = $con;
        $this->comment = $comment;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create()
    {
        if (!$this->userLoggedInObj)
            return "";
        $replyButton = $this->createReplyButton();
        $likesCount = $this->createLikesCount();
        $likeButton = $this->createLikeButton();
        $dislikeButton = $this->createDislikeButton();
        $replySection = $this->createReplySection();
        if ($this->comment->getPostedBy() == $this->userLoggedInObj->getUsername())
            $deleteButton = $this->createDeleteButton();
        else
            $deleteButton = null;


        return "<div class='controls'>
                " . ($deleteButton ? $deleteButton : "
                    $replyButton
                    $likesCount
                    $likeButton
                    $dislikeButton") . "
                </div>
                $replySection";
    }

    private function createReplyButton()
    {
        $text = "REPLY";
        $action = "toggleReply(this)";

        return ButtonProvider::createButton($text, null, $action, 'button-inactive');
    }

    private function createLikesCount()
    {
        $text = $this->comment->getLikes();

        if ($text == 0) $text = "";

        return "<span class='likesCount'>$text</span>";
    }

    private function createReplySection()
    {
        if (!$this->userLoggedInObj)
            return "<div class='commentForm hidden'></div>";
        $postedBy = $this->userLoggedInObj->getUsername();
        $videoId = $this->comment->getVideoId();
        $commentId = $this->comment->getId();

        $profileButton = ButtonProvider::createUserProfileButton($this->con, $postedBy);

        $cancelButtonAction = "toggleReply(this)";
        $cancelButton = ButtonProvider::createButton("Cancel", null, $cancelButtonAction, "btn btn-outline-warning btn-sm");

        $postButtonAction = "postComment(this, \"$postedBy\", $videoId, $commentId, \"repliesSection\")";
        $postButton = ButtonProvider::createButton("Reply", null, $postButtonAction, "btn btn-outline-warning btn-sm");

        if ($this->comment->getPostedBy() == $this->userLoggedInObj->getUsername())
            $deleteButton = $this->createDeleteButton();
        else
            $deleteButton = null;

        return "<div class='commentForm hidden mt-2 ms-4 ms-md-5'>
                    $profileButton
                 <div class='row w-100'>
                    <div class='col-12 col-sm-12 emoji-picker-container text-area-container'>
                        <textarea class='commentBodyClass w-100' placeholder='Add a public comment' data-emojiable='true'></textarea>
                    </div>
                    <div class='col-12 col-sm-12 text-start'>
                        $cancelButton
                        $postButton
                    </div>
                 </div>
                </div>";
    }

    private function createLikeButton()
    {
        $commentId = $this->comment->getId();
        $videoId = $this->comment->getVideoId();
        $action = "likeComment($commentId, this, $videoId)";
        $class = "button-inactive";

        $imageSrc = "assets/images/icons/thumb-up.png";

        if ($this->comment->wasLikedBy()) {
            $class = "button-active";
            $imageSrc = "assets/images/icons/thumb-up-active.png";
        }

        return ButtonProvider::createButton("", $imageSrc, $action, $class);
    }

    private function createDislikeButton()
    {
        $commentId = $this->comment->getId();
        $videoId = $this->comment->getVideoId();
        $action = "dislikeComment($commentId, this, $videoId)";
        $class = "button-inactive rotate-180";

        $imageSrc = "assets/images/icons/thumb-up.png";

        if ($this->comment->wasDislikedBy()) {
            $class = "button-active rotate-180";
            $imageSrc = "assets/images/icons/thumb-up-active.png";
        }

        return ButtonProvider::createButton("", $imageSrc, $action, $class);
    }

    private function createDeleteButton()
    {
        $commentId = $this->comment->getId();
        $videoId = $this->comment->getVideoId();
        $action = "deleteComment($commentId, $videoId)";


        return ButtonProvider::createButton("Delete", null, $action, "btn-danger");
    }
}

?>