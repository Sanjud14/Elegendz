<?php
$pageTitle = "EZ News";
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php');
require_once("includes/classes/BlogPost.php");
const POSTS_PER_PAGE = 10;
$tags = BlogPost::getAllTags($con);
//get news posts
$searchTag = isset($_GET['tag']) ? $_GET['tag'] : null;
$postId = isset($_GET['id']) ? $_GET['id'] : null;
$offset = isset($_GET['offset']) ? $_GET['offset'] : null;
//get number of posts
$query = $con->prepare("SELECT count(blog_posts.id) as 'count' FROM blog_posts LEFT JOIN blog_posts_tags ON blog_posts_tags.post_id = blog_posts.id LEFT JOIN blog_tags ON blog_tags.id = blog_posts_tags.tag_id
" . ($searchTag ? ' WHERE blog_tags.id = :tagId' : '') . ($postId ? 'WHERE blog_posts.id = :postId' : '') . ' ');
if ($searchTag)
    $query->bindParam(":tagId", $searchTag);
if ($postId)
    $query->bindParam(":postId", $postId);
$query->execute();
$total = $query->fetchColumn();

$query = $con->prepare("SELECT blog_posts.*,users.id AS user_id,users.username AS user_name FROM blog_posts LEFT JOIN blog_posts_tags ON blog_posts_tags.post_id = blog_posts.id
    LEFT JOIN blog_tags ON blog_tags.id = blog_posts_tags.tag_id LEFT JOIN users ON users.id = blog_posts.created_by
" . ($searchTag ? ' WHERE blog_tags.id = :tagId' : '') . ($postId ? 'WHERE blog_posts.id = :postId' : '') . "
GROUP BY blog_posts.id ORDER BY blog_posts.id DESC  LIMIT " . ($offset ? "$offset," : "") . POSTS_PER_PAGE);
if ($searchTag)
    $query->bindParam(":tagId", $searchTag);
if ($postId)
    $query->bindParam(":postId", $postId);
$query->execute();

$news = $query->fetchAll();

if ($postId && sizeof($news) == 1)
    $uniquePost = $news[0];
else
    $uniquePost = null;
define("OG_TITLE", $uniquePost ? $uniquePost['title'] : "E Legendz News");

const OG_TYPE = 'news.reads';
define("OG_DESCRIPTION", $uniquePost ? (substr(strip_tags($uniquePost['content']), 0, 150) . '...') : "Latest news from E Legendz artists and contests");
//try to get some image from the posts
foreach ($news as $post) {
    if ($post['main_image']) {
        define("OG_IMAGE", $post['main_image']);
        break;
    }
}
require_once("includes/header.php");

?>
    <h1>E Legendz News</h1>
    <div class="row">
        <div class="col-12 col-md-11 col-lg-10 col-xl-9">
            <?php if (sizeof($news) == 0) { ?>
                <p>No news yet.</p>
            <?php } ?>
            <?php foreach ($news as $postArray) {
                $post = new BlogPost($con, $postArray, $userLoggedInObj);
                ?>
                <div class="news-post">
                    <h2><?php echo $post->getTitle() ?></h2>
                    <?php if ($post->getMainImage()) { ?>
                        <img src="<?php echo $post->getMainImage() ?>" class="img-fluid mx-auto d-block"/>
                    <?php } ?>
                    <article class="">
                        <?php echo $post->getContent(); ?>
                        <?php $postTags = $post->getTagsArray() ?>
                        <?php foreach ($postTags as $postTag) { ?>
                            <a href="/news?tag=<?php echo $postTag['id'] ?>"><span
                                        class="badge rounded-pill bg-warning text-dark"><?php echo $postTag['keyword'] ?></span></a>

                        <?php } ?>
                        <div class="post-data">
                            <a href="/<?php echo $postArray['user_name'] ?>"><?php echo $postArray['user_name'] ?></a>
                            - <?php echo date("F j, Y, g:i a", strtotime($post->getCreatedAt())) ?>
                        </div>
                    </article>
                </div>
            <?php } ?>
            <?php
            if ($offset == null)
                $offset = 0;
            if ($total > POSTS_PER_PAGE) { ?>
                <div class="container-fluid">
                    <div class="row mb-3" id="news_pagination">
                        <?php if (($offset + POSTS_PER_PAGE) < $total) { ?>
                            <div class="col text-center">
                                <a href="/news?offset=<?php echo ($offset + POSTS_PER_PAGE) . ($searchTag ? ('&tag=' . $searchTag) : '') ?>">
                                    <i class="bi bi-arrow-left-short"></i> View older news
                                </a>
                            </div>
                        <?php } ?>
                        <?php if (($offset > 0)) { ?>
                            <div class="col text-center">
                                <a href="/news?offset=<?php echo ($offset - POSTS_PER_PAGE) . ($searchTag ? ('&tag=' . $searchTag) : '') ?>">
                                    View newer news <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class=" col-12 col-md-1 col-lg-2 col-xl-3">
            <h2>Tags</h2>
            <?php foreach ($tags as $tag) { ?>
                <a href="/news?tag=<?php echo $tag['id'] ?>"><span
                            class="badge rounded-pill bg-warning text-dark"><?php echo $tag['keyword'] ?></span></a>
            <?php } ?>
        </div>
    </div>

<?php if ($postId == null) { ?>
    <script type="text/javascript" src="/assets/js/readmore.js"></script>
    <script type="text/javascript">
        $('article').each(function () {
            if ($(this).height() > 200) {
                $('article').readmore({
                    speed: 500,
                    lessLink: '<div class="w-100 text-center"><a href="#" class="read-link">Read less</a></div>',
                    moreLink: '<div class="w-100 text-center"><a href="#" class="read-link">Read more...</a></div>',
                    // Add the class 'transitioning' before toggling begins.
                    beforeToggle: function (trigger, element, expanded) {
                        element.addClass('transitioning');

                    },
                    // Remove the 'transitioning' class when toggling completes.
                    afterToggle: function (trigger, element, expanded) {
                        element.removeClass('transitioning');

                    }
                });
            }
        });

        /*  $(window).on('resize', function () {
              var $reader = $('.quote-heading').readmore({
                  speed: 75,
                  lessLink: '<a href="#">Less</a>',
                  moreLink: '<a href="#">Read more...</a>',
                  collapsedHeight: 100,
                  embedCSS: true
              });

              if ($(window).width() < 640) {
                  $reader.readmore('destroy');
              }
          }).trigger('resize');*/
    </script>
<?php } ?>

<?php require_once("includes/footer.php"); ?>
