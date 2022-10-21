function postComment(button, postedBy, videoId, replyTo, containerClass) {
    var textarea = $(button).parent().parent().find('textarea');
    var commentText = textarea.val();
    textarea.val("");

    if (commentText) {

        $.post("ajax/postComment.php", {
            commentText: commentText, postedBy: postedBy,
            videoId: videoId, responseTo: replyTo
        })
            .done(function (comment) {

                if (!replyTo) {
                    $("." + containerClass).prepend(comment);
                } else {
                    //console.log($(button).parent().parent().parent().parent(),$(button).parent().parent().parent().parent().find("." + containerClass));
                    $(button).parent().parent().parent().parent().find("." + containerClass).append(comment);
                }
                //empty comment input
                $(button).parent().parent().find('div textarea').val("");
                $(button).parent().parent().find('div .commentBodyClass').html("");

            });

    } else {
        alert("You can't post an empty comment");
    }
}

function toggleReply(button) {
    var parent = $(button).closest(".itemContainer");
    var commentForm = parent.find(".commentForm").first();

    commentForm.toggleClass("hidden");

    window.emojiPicker = new EmojiPicker({
        emojiable_selector: '[data-emojiable=true]',
        assetsPath: '/assets/images/emoji-picker',
        popupButtonClasses: 'fa fa-smile-o'
    });
    window.emojiPicker.discover();
}

function likeComment(commentId, button, videoId) {
    $.post("ajax/likeComment.php", {commentId: commentId, videoId: videoId})
        .done(function (numToChange) {

            var likeButton = $(button);
            var dislikeButton = $(button).siblings(".dislikeButton");

            likeButton.addClass("active");
            dislikeButton.removeClass("active");

            var likesCount = $(button).siblings(".likesCount");
            updateLikesValue(likesCount, numToChange);

            if (numToChange < 0) {
                likeButton.removeClass("active");
                likeButton.removeClass("button-active");
                likeButton.addClass("button-inactive");
                likeButton.find("img:first").attr("src", "assets/images/icons/thumb-up.png");
            } else {
                likeButton.removeClass("button-inactive");
                likeButton.addClass("button-active");
                likeButton.find("img:first").attr("src", "assets/images/icons/thumb-up-active.png")
            }

            dislikeButton.find("img:first").attr("src", "assets/images/icons/thumb-down.png");
        });
}

function dislikeComment(commentId, button, videoId) {
    $.post("ajax/dislikeComment.php", {commentId: commentId, videoId: videoId})
        .done(function (numToChange) {

            var dislikeButton = $(button);
            var likeButton = $(button).siblings(".likeButton");

            dislikeButton.addClass("active");
            likeButton.removeClass("active");

            var likesCount = $(button).siblings(".likesCount");
            updateLikesValue(likesCount, numToChange);

            if (numToChange > 0) {
                dislikeButton.removeClass("active");
                dislikeButton.removeClass("button-active");
                dislikeButton.addClass("button-inactive");
                dislikeButton.find("img:first").attr("src", "assets/images/icons/thumb-up.png");
            } else {
                dislikeButton.removeClass("button-inactive");
                dislikeButton.addClass("button-active");
                dislikeButton.find("img:first").attr("src", "assets/images/icons/thumb-up-active.png")
            }

            likeButton.find("img:first").attr("src", "assets/images/icons/thumb-up.png");
        });
}

function updateLikesValue(element, num) {
    var likesCountVal = element.text() || 0;
    element.text(parseInt(likesCountVal) + parseInt(num));
}

function getReplies(commentId, button, videoId) {
    $.post("ajax/getCommentReplies.php", {commentId: commentId, videoId: videoId})
        .done(function (comments) {
            var replies = $("<div>").addClass("repliesSection");
            replies.append(comments);

            $(button).replaceWith(replies);
        });
}

function deleteComment(commentId, videoId) {
    if (confirm("Are you sure you want to delete this comment?"))
        $.post("ajax/deleteComment.php", {commentId: commentId, videoId: videoId})
            .done(function (result) {
                if (parseInt(result) === 1)
                    $('#comment_' + commentId).remove();
                else {
                    alert("Error deleting comment!");
                    console.log(result);
                }
                /* var replies = $("<div>").addClass("repliesSection");
                 replies.append(comments);

                 $(button).replaceWith(replies);*/
            });
}