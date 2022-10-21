<?php
$section = "news";
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/BlogPost.php');
$newsPost = null;
$users = User::getAllUsers($con);
$tags = BlogPost::getAllTags($con);
if (isset($id)) {
    $newsPost = new BlogPost($con, $id, $userLoggedInObj);
    if (!$newsPost) {
        echo "post not found!";
        exit;
    }
}
if (isset($_POST['content'])) {

    //image upload
    $mainImagePath = $newsPost ? $newsPost->getMainImage() : null;
    $uploadOk = 1;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['name'] != '') {
        $target_dir = "/assets/images/news/";
        $target_file = $target_dir . basename($_FILES["main_image"]["name"]);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $destinationName = $target_dir . uniqid() . '.' . $imageFileType;
        // Check if image file is a actual image or fake image

        $check = getimagesize($_FILES["main_image"]["tmp_name"]);
        if ($check !== false) {
            // echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
            move_uploaded_file($_FILES["main_image"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $destinationName);
            $mainImagePath = $destinationName;
            if ($newsPost && $newsPost->getMainImage()) {
                //remove previous
                @unlink($_SERVER['DOCUMENT_ROOT'] . $newsPost->getMainImage());
            }
        } else {
            $_SESSION['message_display'] = 'File is not an image';
            $_SESSION['message_display_type'] = 'danger';
            $uploadOk = 0;
        }

    }
    //save
    if ($uploadOk == 1) {
        $content = BlogPost::addProfileLinks($_POST['content']);
        if ($newsPost) {//UPDATE
            $query = $con->prepare("UPDATE blog_posts SET title = :title, content = :content, main_image = :mainImage, updated_at = NOW()
                            WHERE id = :id");
            $query->bindParam(":title", $_POST['title']);
            $query->bindParam(":content", $content);
            $query->bindParam(":mainImage", $mainImagePath);
            $modifyPostId = $newsPost->getId();
            $query->bindParam(":id", $modifyPostId);
            $query->execute();
        } else {//CREATE
            $query = $con->prepare("INSERT INTO blog_posts(title, content, main_image, created_at, updated_at, created_by)
                             VALUES(:title, :content, :mainImage, NOW(), NOW(),:userId);");
            $query->bindParam(":title", $_POST['title']);
            $query->bindParam(":content", $content);
            $query->bindParam(":mainImage", $mainImagePath);
            $query->bindParam(":userId", $userLoggedInObj->getId());
            $con->beginTransaction();
            $query->execute();

            $postId = $con->lastInsertId();
            $con->commit();
            $newsPost = new BlogPost($con, $postId, $userLoggedInObj);
        }
        //tags
        if ($newsPost) {//DELETE EXISTING, TO RE-CREATE
            $query = $con->prepare("DELETE FROM blog_posts_tags WHERE post_id=:postId");
            $postId = $newsPost->getId();
            $query->bindParam(":postId", $postId);
            $query->execute();

        }
        $tagsText = $_POST['tags'];
        $tagsParts = explode(',', $tagsText);
        foreach ($tagsParts as $tag) {
            $newsPost->registerTag(trim($tag));
        }

        $_SESSION['message_display'] = 'News post was succesfully ' . (isset($id) ? 'updated' : 'created');
        $_SESSION['message_display_type'] = 'success';
        header("Location: /admin/news");
        exit;
    }
}


?>
<!--<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>-->
<script src="/assets/js/ckeditor.js"></script>

<div class="container-fluid pt-4 px-4">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/_message_display.php'; ?>
    <div class="row bg-light rounded  mx-0">
        <div class="col-md-12 ">
            <div class="bg-light rounded p-4">
                <h6 class="mb-4"><?php echo($newsPost ? "Edit News Post" : "Create News Post") ?></h6>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?php echo(isset($_POST['title']) ? $_POST['title'] : ($newsPost ? $newsPost->getTitle() : '')) ?>"
                               maxlength="511"
                               required/>
                    </div>
                    <div class="mb-3">
                        <label for="main_image"
                               class="form-label"><?php if ($newsPost && $newsPost->getMainImage()) echo 'Replace ' ?>
                            Main Image</label>
                        <?php if ($newsPost && $newsPost->getMainImage()) { ?>
                            <br/>
                            <img src="<?php echo $newsPost->getMainImage() ?>" class="thumb-version mb-2"/>
                        <?php } ?>
                        <input type="file" class="form-control" id="main_image" name="main_image"/>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea type="file" class="form-control" id="content" name="content"
                                  rows="6"><?php echo(isset($_POST['content']) ? $_POST['content'] : ($newsPost ? $newsPost->getContent() : '')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags"
                               value="<?php echo(isset($_POST['tags']) ? $_POST['tags'] : ($newsPost ? $newsPost->getTagsString() : '')) ?>"/>
                        <div id="tags_help" class="form-text">Enter tags names separated by comma
                        </div>
                        <div id="tags_list">
                            <label for="" class="form-label">Existing Tags</label><br/>
                            <?php foreach ($tags as $tag) { ?>
                                <span class="badge rounded-pill bg-warning text-dark"><?php echo $tag['keyword'] ?></span>
                            <?php } ?>

                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    var users = [
        {username: 'dbristylez ', state: ''},
        {username: 'namewithspaces', state: 'NY'},
    ];

    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'fontColor', 'link', 'bulletedList', 'numberedList',
                '|', 'Outdent', 'Indent', '|', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'],
            mention: {
                feeds: [
                    {
                        marker: '@',
                        feed: [
                            <?php foreach ($users as $user) {
                            echo "'@" . $user['username'] . "',";
                        } ?>],
                        minimumCharacters: 1
                    }
                ]
            }
        })
        .then(editor => {
            console.log(editor);

        })
        .catch(error => {
            console.error(error);
        });

    $(document).ready(function () {

    })
</script>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/footer.php'); ?>

