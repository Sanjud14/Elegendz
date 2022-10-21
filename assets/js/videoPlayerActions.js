var likeInProcess = false;

function likeVideo(button, videoId) {
    if (likeInProcess) //don't process another like if the previous isn't done
        return;
    likeInProcess = true;
    $('.likeButton').prop('disabled', true);

    $.post("ajax/likeVideo.php", {videoId: videoId})
        .done(function (data) {
            //console.log(data);
            var likeButton = $(button);
            //  var dislikeButton = $(button).siblings(".dislikeButton");

            likeButton.addClass("active");
            //dislikeButton.removeClass("active");
            console.log(data);
            var result = JSON.parse(data);
            updateLikesValue(likeButton.find(".text #likes"), result.likes);
            //updateLikesValue(dislikeButton.find(".text"), result.dislikes);

            if (result.likes < 0) {
                likeButton.removeClass("active");
                likeButton.css("backgroundColor", "#eac117");
                likeButton.find("span").css("color", "#320550");
                //likeButton.find("img:first").attr("src", "assets/images/icons/thumb-up.png");
            } else {
                likeButton.css("backgroundColor", "#320550");
                likeButton.find("span").css("color", "#eac117");
                // likeButton.find("img:first").attr("src", "assets/images/icons/thumb-up-active.png")
            }
            $('.likeButton').prop('disabled', false);
            //dislikeButton.find("img:first").attr("src", "assets/images/icons/thumb-down.png");
            likeInProcess = false;
        });
}

/*function dislikeVideo(button, videoId) {
    $.post("ajax/dislikeVideo.php", {videoId: videoId})
        .done(function (data) {

            var dislikeButton = $(button);
            var likeButton = $(button).siblings(".likeButton");

            dislikeButton.addClass("active");
            likeButton.removeClass("active");

            var result = JSON.parse(data);
            updateLikesValue(likeButton.find(".text"), result.likes);
            //   updateLikesValue(dislikeButton.find(".text"), result.dislikes);

            if (result.dislikes < 0) {
                dislikeButton.removeClass("active");
                dislikeButton.find("img:first").attr("src", "assets/images/icons/thumb-down.png");
            } else {
                dislikeButton.find("img:first").attr("src", "assets/images/icons/thumb-down-active.png")
            }

            likeButton.find("img:first").attr("src", "assets/images/icons/thumb-up.png");
        });
}*/

function updateLikesValue(element, num) {
    var likesCountVal = element.text() || 0;
    element.text(parseInt(likesCountVal) + parseInt(num));
}

function copyVideoLinkToClipBoard(text) {
    var shareButton = document.getElementById('share_button');
    shareButton.style.backgroundColor = '#320550';
    shareButton.childNodes[1].style.color = '#eac117';
    navigator.clipboard.writeText(text);

    let alert = `<div class="crappy-alert">Copied</div>`;
    let div = document.getElementById('alert');
    div.insertAdjacentHTML('beforeend', alert);

    $("#alert").show().delay(7000).queue(function (n) {
        $(this).hide();
        n();
    });
    //  document.getElementById('message').innerHTML = "URL copied!";
}

function addToPlaylist(songId) {
    console.log(songId);
}

