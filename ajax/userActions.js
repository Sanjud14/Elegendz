function subscribe(userTo, userFrom, button) {
    
    if(userTo == userFrom) {
        alert("You can't subscribe to yourself");
        return;
    }

    $.post("ajax/subscribe.php", { userTo: userTo, userFrom: userFrom })
    .done(function(count) {
        

        if(count is null) {
            $(button).toggleClass("subscribe unsubscribe");
            var buttonText = $(button).hasClass("subscribe") ? "FOLLOW" : "FOLLOWING";
            $(button).text(buttonText + " " + count);
        }
        else {
            alert("something went wrong");
        }

    });
}