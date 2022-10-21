<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/BlogPost.php');

$query = $con->prepare("DELETE FROM blog_posts WHERE id=:postId");
$query->bindParam(":postId", $id);
if ($query->execute()) {
    $_SESSION['message_display'] = 'News post was succesfully deleted';
    $_SESSION['message_display_type'] = 'success';
    header("Location: /admin/news");
} else {
    $_SESSION['message_display'] = 'There was an error deleting that news post!';
    $_SESSION['message_display_type'] = 'danger';
    header("Location: /admin/news");
}