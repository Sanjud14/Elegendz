<?php
$section = "news";
require_once('../header.php');
$query = $con->prepare("SELECT * FROM blog_posts ORDER BY id DESC");
$query->execute();

$news = $query->fetchAll();
?>

    <div class="container-fluid pt-4 px-4">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/_message_display.php'; ?>
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">
                    <h6 class="mb-4">News</h6>
                    <div class="button-panel text-end mb-3">
                        <a href="/admin/news/create" type="button" class="btn btn-warning  ">Add New Post</a>
                    </div>
                    <div class="table-responsive" id="news_table">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">

                            <tbody>
                            <?php
                            if (sizeof($news) == 0)
                                echo '<tr><td colspan="3">No news yet.</td></tr>';
                            else
                                foreach ($news as $newsPost) { ?>
                                    <tr>
                                        <td>
                                            <span class="d-none d-sm-inline"><?php echo date("M jS, Y", strtotime($newsPost['created_at'])); ?></span>
                                            <span class="d-inline d-sm-none"><?php echo date("n/j/y", strtotime($newsPost['created_at'])); ?></span>
                                        </td>
                                        <td>
                                            <?php echo $newsPost['title'] ?>
                                        </td>
                                        <td class="actions-cell">
                                            <a class="btn btn-sm btn-primary"
                                               href="/news?id=<?php echo $newsPost['id'] ?>">View</a>
                                            <a class="btn btn-sm btn-warning"
                                               href="/admin/news/edit/<?php echo $newsPost['id'] ?>">Edit</a>
                                            <a class="btn btn-sm btn-danger" href=""
                                               onclick="deletePost(<?php echo $newsPost['id'] ?>)">Delete</a>
                                        </td>
                                    </tr>
                                <?php }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        /* const NewsTable = {
             setup(props) {

                 return {

                 };
             }
         };
         Vue.createApp(NewsTable).mount('#news_table')*/

        function deletePost(id) {
            if (confirm("Are you sure you want to delete this post?")) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", '/admin/news/delete/' + id, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
                xhr.send();
            }
        }
    </script>
<?php require_once('../footer.php'); ?>