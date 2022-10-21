<?php
$section = "Edit Banner";
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/Sponsor.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/User.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/Account.php');
$sponsor = new Sponsor($con, $userLoggedInObj->getSponsorId(), $userLoggedInObj);
if (isset($_POST['name'])) {
    //first process image

    $logoPath = null;

    if ($_POST['zipcode'] && !Sponsor::isZipcodeValid($_POST['zipcode'], $con)) {
        $_SESSION['message_display'] = 'Not a valid USA zipcode';
        $_SESSION['message_display_type'] = 'danger';
    }

    if ((!$_POST['address'] && $_POST['zipcode']) || ($_POST['address'] && !$_POST['zipcode'])) {
        $_SESSION['message_display'] = 'Both street address and zipcode are required for the address';
        $_SESSION['message_display_type'] = 'danger';
    }

    if (!isset($_SESSION['message_display'])) {//quick way to check if there were no previous errors
        //check if current logo should be deleted
        if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == 1) {
            $query = $con->prepare("UPDATE sponsors SET logo = NULL WHERE id = :id ");
            $id = $sponsor->getId();
            $query->bindParam(":id", $id);

            $con->beginTransaction();
            $query->execute();

            $con->commit();
            @unlink($_SERVER['DOCUMENT_ROOT'] . $sponsor->getLogo());
            //reload
            $sponsor = new Sponsor($con, $userLoggedInObj->getSponsorId(), $userLoggedInObj);
        }

        // Check if image file is a actual image or fake image

        if (!isset($_FILES['logo']) || ($_FILES['logo']['size'] == 0 && ($_FILES['logo']['error'] == 0 || $_FILES['logo']['error'] == 4)))
            $uploadOk = 1; //no upload
        else {
            $target_dir = "/assets/images/sponsors/";
            $target_file = $target_dir . basename($_FILES["logo"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $destinationName = $target_dir . uniqid() . '.' . $imageFileType;

            $check = getimagesize($_FILES["logo"]["tmp_name"]);
            if ($check !== false) {
                // echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
                move_uploaded_file($_FILES["logo"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $destinationName);
                $logoPath = $destinationName;

                //delete previous image
                if ($sponsor->getLogo())
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $sponsor->getLogo());

            } else {
                $_SESSION['message_display'] = 'File is not an image';
                $_SESSION['message_display_type'] = 'danger';
                $uploadOk = 0;
            }
        }

        if ($uploadOk) {
            $query = $con->prepare("UPDATE sponsors SET name = :name, business_pitch = :businessPitch, phone = :phone, address = :address, 
             background_color = :backgroundColor, font_color = :fontColor,  zipcode = :zipcode, url = :url, " . ($logoPath ? "logo=:logo," : "") .
                " business_type = :businessType WHERE id = :id ");
            $query->bindParam(":name", $_POST['name']);
            $query->bindParam(":businessPitch", $_POST['business_pitch']);
            $query->bindParam(":phone", $_POST['phone']);
            $query->bindParam(":address", $_POST['address']);
            $query->bindParam(":backgroundColor", $_POST['background_color']);
            $query->bindParam(":fontColor", $_POST['font_color']);
            $query->bindParam(":zipcode", $_POST['zipcode']);
            $query->bindParam(":url", $_POST['url']);
            if ($logoPath)
                $query->bindParam(":logo", $logoPath);
            $query->bindParam(":businessType", $_POST['business_type']);
            $id = $sponsor->getId();
            $query->bindParam(":id", $id);
            $con->beginTransaction();
            $query->execute();

            $con->commit();

            //reload
            $sponsor = new Sponsor($con, $userLoggedInObj->getSponsorId(), $userLoggedInObj);

            $_SESSION['message_display'] = 'Successfully updated';
            $_SESSION['message_display_type'] = 'success';
        }

    }
}
?>
<div class="container-fluid pt-4 px-2 px-sm-4">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/_message_display.php'; ?>
    <div class="row g-4">
        <div class="col-sm-12 col-xl-12">
            <div class="bg-light rounded h-100 p-4">
                <h5 class="mb-4">Edit Banner</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="mb-3 col-12 col-sm-6">
                            <label for="name" class="form-label">Business Name</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="30"
                                   value="<?php echo(isset($_POST['name']) ? $_POST['name'] : $sponsor->getName()) ?>"/>
                        </div>
                        <div class="mb-3 col-12 col-sm-6">
                            <label for="business_type" class="form-label">Business Type</label>
                            <input type="text" class="form-control" id="business_type" name="business_type"
                                   value="<?php echo(isset($_POST['business_type']) ? $_POST['business_type'] : $sponsor->getBusinessType()) ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-12 ">
                            <label for="business_pitch" class="form-label">Slogan / Message</label>
                            <input type="text" class="form-control" id="business_pitch" name="business_pitch"
                                   value="<?php echo(isset($_POST['business_pitch']) ? $_POST['business_pitch'] : $sponsor->getBusinessPitch()) ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-12 col-sm-6">
                            <label for="address" class="form-label">Address (optional)</label>
                            <div class="input-group">
                                <input type="text" aria-label="Street Address" name="address" id="address"
                                       maxlength="120"
                                       class="form-control"
                                       value="<?php echo(isset($_POST['address']) ? $_POST['address'] : $sponsor->getAddress()) ?>"
                                       placeholder="Street Address">
                                <input type="text" aria-label="Zipcode" class="form-control" placeholder="Zipcode"
                                       name="zipcode"
                                       id="zipcode"
                                       value="<?php echo(isset($_POST['zipcode']) ? $_POST['zipcode'] : $sponsor->getZipcode()) ?>">
                            </div>

                        </div>
                        <div class="mb-3 col-12 col-sm-6">
                            <label for="phone" class="form-label">Phone (optional)</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?php echo(isset($_POST['phone']) ? $_POST['phone'] : $sponsor->getPhone()) ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-12 col-sm-12">
                            <label for="background_color" class="form-label">URL (optional)</label>
                            <input type="text" name="url" id="url" class="form-control"
                                   value="<?php echo(isset($_POST['url']) ? $_POST['url'] : $sponsor->getUrl()) ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-12 ">
                            <label for="logo" class="form-label">Logo (recommended 80 x 52)</label>
                            <br/>
                            <?php if ($sponsor->getLogo()) { ?>
                                <img src="<?php echo $sponsor->getLogo(); ?>" id="logo_preview" class="mb-3"/>
                                <br/>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo"
                                           value="1">
                                    <label class="form-check-label" for="remove_logo">Remove current logo</label>
                                </div>
                            <?php } ?>
                            <input type="file" class="form-control" name="logo" id="logo"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-12 col-sm-6">
                            <label for="background_color" class="form-label">Background Color</label>
                            <input type="text" name="background_color" id="background_color"
                                   data-jscolor="{onChange: 'updateColor(this, \'#background_color\')',}"
                                   class="form-control" required="required"
                                   value="<?php echo(isset($_POST['background_color']) ? $_POST['background_color'] : $sponsor->getBackgroundColor()) ?>"/>
                        </div>
                        <div class="mb-3 col-12 col-sm-6">
                            <label for="font_color" class="form-label">Font Color</label>
                            <input type="text" name="font_color" id="font_color"
                                   data-jscolor="{onChange: 'updateColor(this, \'#font_color\')',}" class="form-control"
                                   required="required"
                                   value="<?php echo(isset($_POST['font_color']) ? $_POST['font_color'] : $sponsor->getFontColor()) ?>"/>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">Update</button>


                </form>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="mb-3 col-12 text-center">
            <h6 class="form-label text-center">Banner Preview</h6>
            <?php include ($_SERVER['DOCUMENT_ROOT']) . '/includes/_banner.php' ?>
        </div>
    </div>

</div>

<script type="text/javascript">
    function updateColor(picker, selector) {
        // console.log(document.querySelector(selector));
        document.querySelector(selector).value = picker.toString();
    }
</script>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/footer.php'); ?>

