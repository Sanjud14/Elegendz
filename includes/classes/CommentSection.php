<?php

class CommentSection
{

    private $con, $video, $userLoggedInObj;

    public function __construct($con, $video, $userLoggedInObj)
    {
        $this->con = $con;
        $this->video = $video;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create()
    {
        return $this->createCommentSection();
    }

    private function createCommentSection()
    {
        if (!$this->userLoggedInObj)
            $loginNotice = "<p><a href='/sign-in'>Log in</a> to add a comment</p>";
        else
            $loginNotice = "";
        $numComments = $this->video->getNumberOfComments();
        $postedBy = $this->userLoggedInObj ? $this->userLoggedInObj->getUsername() : null;
        $videoId = $this->video->getId();

        $profileButton = $this->userLoggedInObj ? ButtonProvider::createUserProfileButton($this->con, $postedBy) : "";
        $commentAction = "postComment(this, \"$postedBy\", $videoId, null, \"comments\")";
        $commentButton = ButtonProvider::createButton("COMMENT", null, $commentAction, "btn btn-outline-warning btn-sm");
        if ($this->userLoggedInObj)
            $commentForm = "<div class='commentForm'>
                            $profileButton
                            <div class='row w-100'>
                                <div class='col-12 col-sm-12 emoji-picker-container text-area-container'>
                                    <textarea class='commentBodyClass w-100' placeholder='Add a public comment' data-emojiable='true'></textarea></div>
                                   
                                <div class='col-12 col-sm-12 text-start'>
                                    $commentButton
                                </div>
                            </div>
                            
      
                        </div>";
        else
            $commentForm = "";

        $comments = $this->video->getComments();
        $commentItems = "";
        foreach ($comments as $comment) {
            $commentItems .= $comment->create();
        }

        return "<div class='commentSection'>
                    $loginNotice
                    <div class='header'>
                        <span class='commentCount'>$numComments Comments</span>
                        $commentForm
                    </div>

                    <div class='comments'>
                        $commentItems
                    </div>

                </div>";
    }

}

?>