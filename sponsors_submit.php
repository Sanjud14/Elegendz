<?php
$section = "Sponsorship Application";
require_once("includes/header.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/Constants.php");

$account = new Account($con);
$sponsorErrors = [];

if (isset($_POST['submit_button'])) {


    if ($_POST['address'] && !$_POST['zipcode'])
        $sponsorErrors[] = Constants::$zipcodeRequiredWithAddress;
    else {
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


            } else {
                $_SESSION['message_display'] = 'File is not an image';
                $_SESSION['message_display_type'] = 'danger';
                $uploadOk = 0;
            }
        }

        if ($uploadOk) {
            //create user
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $zipcode = trim($_POST['zipcode']);
            $url = trim($_POST['url']);
            $user = $account->register($username, $email, $email, $password, $password, $zipcode, true);
            //create in database
            if ($user) {
                $query = $con->prepare("INSERT INTO sponsors(name, business_pitch, phone, address,logo, business_type,zipcode, url )
                             VALUES(:name, :businessPitch, :phone,:address,:logo, :businessType,:zipcode,:url);");
                $query->bindParam(":name", $username);
                $query->bindParam(":businessPitch", $_POST['business_pitch']);
                $query->bindParam(":phone", $_POST['phone']);
                $query->bindParam(":address", $_POST['address']);
                $query->bindParam(":logo", $logoPath);
                $query->bindParam(":businessType", $_POST['business_type']);
                $query->bindParam(":zipcode", $zipcode);
                $query->bindParam(":url", $url);
                $con->beginTransaction();
                $query->execute();

                $sponsorId = $con->lastInsertId();
                $con->commit();
                //update user
                $query = $con->prepare("UPDATE users SET sponsor_id=:sponsorId WHERE id=:id");
                $query->bindParam(":sponsorId", $sponsorId);
                $userId = $user->getId();
                $query->bindParam(":id", $userId);

                $query->execute();

                $_SESSION['message_display'] = 'In this panel you can edit your banner and modify your subscription. You can now proceed to select the zip codes of your target audience.';
                $_SESSION['message_display_type'] = 'success';

                $_SESSION["userLoggedIn"] = $username;
                $_SESSION["userRole"] = $user->getRole();
                $_SESSION["zipcode"] = $user->getZipcode();

                header("Location: /sponsor/index");


                //header("Location: /sign-in");

            }
        }
    }
}

function getInputValue($name)
{
    if (isset($_POST[$name])) {
        echo $_POST[$name];
    }
}

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/_message_display.php'; ?>
<div class="window-box" id="sponsor_form">
    <h3 class="mb-2">Sponsorship Application</h3>
    <small>Fields <b>in bold</b> will be displayed in the banner. You can modify them later.</small>
    <form action="/sponsor-submit" method="POST" enctype="multipart/form-data">
        <div class="mb-2 row">
            <div class="col-12 col-sm-6">
                <label for="title_input" class="form-label">Username</label>
                <input class="form-control" type="text" name="username" id="username" required="required"
                       value="<?php getInputValue('username'); ?>"
                       autocomplete="off" maxlength="40"/>
                <?php echo $account->getError(Constants::$usernameCharacters); ?>
                <?php echo $account->getError(Constants::$usernameStrangeCharacters); ?>
                <?php echo $account->getError(Constants::$usernameTaken); ?>
            </div>
            <div class="col-12 col-sm-6">
                <label for="title_input" class="form-label">Password</label>
                <input class="form-control" type="password" name="password" id="password" required="required"
                       autocomplete="off" maxlength="40" value="<?php getInputValue('password'); ?>"/>
                <?php echo $account->getError(Constants::$passwordNotAlphanumeric); ?>
                <?php echo $account->getError(Constants::$passwordLength); ?>
            </div>
        </div>
        <div class="mb-2 row">
            <div class="col-12 col-sm-6">
                <label for="title_input" class="form-label">Email</label>
                <input class="form-control" type="text" name="email" id="email" required="required" autocomplete="off"
                       value="<?php getInputValue('email'); ?>"
                       maxlength="200"/>
                <?php echo $account->getError(Constants::$emailInvalid); ?>
                <?php echo $account->getError(Constants::$emailTaken); ?>
            </div>
            <div class="col-12 col-sm-6">
                <label for="title_input" class="form-label"><b>Business Name</b></label>
                <input class="form-control" type="text" name="name" id="name" required="required" maxlength="30"
                       value="<?php getInputValue('name'); ?>"/>
            </div>
        </div>
        <div class="mb-2">
            <label for="title_input" class="form-label"><b>Slogan / Message</b></label>
            <input class="form-control" type="text" name="business_pitch" id="business_pitch"
                   value="<?php getInputValue('business_pitch'); ?>"/>
        </div>
        <div class="mb-2 row">
            <div class="col-12 col-sm-6">
                <label for="title_input" class="form-label">Business Type</label>
                <input class="form-control" type="text" name="business_type" id="business_type"
                       value="<?php getInputValue('business_type'); ?>"/>
            </div>
            <div class="col-12 col-sm-6">
                <label for="title_input" class="form-label"><b>URL (optional)</b></label>
                <input class="form-control" type="text" name="url" id="url"
                       value="<?php getInputValue('url'); ?>"/>
            </div>
        </div>
        <div class="mb-2 row">
            <div class="col-12 col-sm-6">
                <label for="address" class="form-label"><b>Address (optional)</b></label>
                <!-- <input class="form-control" type="text" name="address" id="address" maxlength="120"
                       value="<?php getInputValue('address'); ?>"/> -->
                <div class="input-group">
                    <input type="text" aria-label="Street Address" name="address" id="address" maxlength="120"
                           class="form-control" value="<?php getInputValue('address'); ?>" placeholder="Street Address">
                    <input type="text" aria-label="Zipcode" class="form-control" placeholder="Zipcode" name="zipcode"
                           id="zipcode" value="<?php getInputValue('zipcode'); ?>">
                </div>
                <?php echo $account->getError(Constants::$invalidZipcode); ?>
                <?php if (in_array(Constants::$zipcodeRequiredWithAddress, $sponsorErrors)) { ?>
                    <span class="errorMessage"><?php echo Constants::$zipcodeRequiredWithAddress; ?></span>
                <?php } ?>
            </div>
            <div class="col-12 col-sm-6">
                <label for="phone" class="form-label"><b>Phone (optional)</b></label>
                <input class="form-control" type="text" name="phone" id="phone" maxlength="60"
                       value="<?php getInputValue('phone'); ?>"/>
            </div>
        </div>

        <div class="mb-2">
            <label for="logo" class="form-label"><b>Logo (optional)</b> (recommended 80 x 52)</label>
            <input class="form-control" type="file" name="logo" id="logo" accept="image/*"/>
        </div>

        <button type="submit" class="btn btn-primary submit-button" name="submit_button">SUBMIT</button>
    </form>
</div>
<?php require_once("includes/footer.php"); ?>