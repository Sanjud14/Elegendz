<?php
require_once("ButtonProvider.php");
require_once("CommentControls.php");

class BlogPost
{
    private $con, $sqlData, $userLoggedInObj, $blogPostId;

    public function __construct($con, $input, $userLoggedInObj)
    {

        if (!is_array($input)) {
            $query = $con->prepare("SELECT * FROM blog_posts where id=:id");
            $query->bindParam(":id", $input);
            $query->execute();

            $input = $query->fetch(PDO::FETCH_ASSOC);
        }

        $this->sqlData = $input;
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
        $this->blogPostId = $this->getId();
    }

    public function getId()
    {
        return $this->sqlData["id"];
    }

    public function getMainImage()
    {
        return $this->sqlData["main_image"];
    }

    public function getTitle()
    {
        return $this->sqlData["title"];
    }

    public function getContent()
    {
        return $this->sqlData["content"];
    }

    public function getCreatedAt()
    {
        return $this->sqlData["created_at"];
    }

    /**
     * Returns all tags as array name => id
     * @param $con PDO object
     * @return array
     */
    public static function getAllTags($con)
    {
        $query = $con->prepare("SELECT keyword,blog_tags.id FROM blog_tags INNER JOIN blog_posts_tags ON blog_tags.id = blog_posts_tags.tag_id");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Associates tag with current post, creates it if necessary
     * @param $keyword string
     *
     */
    public function registerTag($keyword)
    {
        $query = $this->con->prepare("SELECT * FROM blog_tags WHERE keyword = :keyword");
        $query->bindParam(":keyword", $keyword);
        $query->execute();
        $tag = $query->fetch(PDO::FETCH_ASSOC);
        if (!$tag) { //must create
            $query = $this->con->prepare("INSERT INTO blog_tags (keyword) VALUES (:keyword)");
            $query->bindParam(":keyword", $keyword);
            $this->con->beginTransaction();
            $query->execute();
            $tagId = $this->con->lastInsertId();
            $this->con->commit();
        } else
            $tagId = $tag['id'];
        //create relation
        $query = $this->con->prepare("INSERT INTO blog_posts_tags (tag_id,post_id) VALUES (:tagId,:postId)");
        $query->bindParam(":tagId", $tagId);
        $postId = $this->getId();
        $query->bindParam(":postId", $postId);
        $query->execute();
    }

    /**
     * Get all post's tags as string separated by commas
     * @return string
     */
    public function getTagsString()
    {
        $query = $this->con->prepare("SELECT keyword FROM blog_tags INNER JOIN blog_posts_tags ON blog_tags.id = blog_posts_tags.tag_id WHERE post_id = :postId ");
        $id = $this->getId();
        $query->bindParam(":postId", $id);
        $query->execute();
        $tags = $query->fetchAll(PDO::FETCH_ASSOC);
        $tagsString = "";
        foreach ($tags as $i => $tag) {
            if ($i > 0)
                $tagsString .= ',';
            $tagsString .= $tag['keyword'];
        }
        return $tagsString;
    }

    /**
     * Get all post's tags as array
     * @return array
     */
    public function getTagsArray()
    {
        $query = $this->con->prepare("SELECT blog_tags.id,keyword FROM blog_tags INNER JOIN blog_posts_tags ON blog_tags.id = blog_posts_tags.tag_id WHERE post_id = :postId ");
        $id = $this->getId();
        $query->bindParam(":postId", $id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Replaces all usernames starting with @ with a link to that user's profile
     * @param $text string
     * @return $text
     */
    public static function addProfileLinks($text)
    {
        $regex = '~(@\w+)~';
        if (preg_match_all($regex, $text, $matches, PREG_PATTERN_ORDER)) {
            foreach ($matches[1] as $name) {
                echo $name . '<br/>';
                $text = str_replace($name, "<a href='/" . ltrim($name, "@") . "'>" . ltrim($name, "@") . "</a>", $text);
            }
        }
        return $text;
    }

}

