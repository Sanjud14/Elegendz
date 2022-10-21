<?php

if (isset($_REQUEST['message_display']) || isset($_SESSION["message_display"])) {
    if (isset($_REQUEST['message_display'])) {
        $message = $_REQUEST['message_display'];
        $type = $_REQUEST['message_display_type'];
    } else {
        $message = $_SESSION['message_display'];
        $type = $_SESSION['message_display_type'];
        unset($_SESSION['message_display']);
        unset($_SESSION['message_display_type']);
    }
    ?>
    <div class="alert alert-<?php if ($type) echo $type; else echo 'primary' ?> alert-dismissable mt-2 rounded-0"
         role="alert">
        <?php echo $message ?>
        <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close">
    </div>
<?php } ?>