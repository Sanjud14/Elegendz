<?php
require_once("includes/header.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/FormSanitizer.php");
require_once("includes/classes/SettingsFormProvider.php");
require_once("includes/classes/Constants.php");

if (!User::isLoggedIn()) {
    header("Location: /sign-in");
}

$detailsMessage = "";
$passwordMessage = "";
$formProvider = new SettingsFormProvider($con);

if (isset($_POST["saveDetailsButton"])) {
    $account = new Account($con);
    $email = FormSanitizer::sanitizeFormString($_POST["email"]);

    if ($account->updateDetails($email, $userLoggedInObj->getUsername())) {
        //update categories
        $query = $con->prepare("SELECT * FROM categories ORDER BY id ASC");
        $query->execute();
        $categories = $query->fetchAll();
        $userCategories = [];
        foreach ($categories as $category) {
            if (isset($_POST[$category['id']]) && $_POST[$category['id']] == 1) {//add relation
                $userCategories[] = $category['id'];
            }
        }
        $userLoggedInObj->saveUserCategories($userCategories);

        $detailsMessage = "<div class='alert alert-success'>
					<strong>Success!</strong> Details updated Successfully!
								</div>";
    } else {
        $errorMessage = $account->getFirstError();


        if ($errorMessage == "") $errorMessage = "Something went wrong";

        $detailsMessage = "<div class='alert alert-danger'>
					<strong>ERROR!</strong> $errorMessage
								</div>";
    }
}

if (isset($_POST["savePasswordButton"])) {

    $account = new Account($con);

    $oldPassword = FormSanitizer::sanitizeFormPassword($_POST["oldPassword"]);
    $newPassword = FormSanitizer::sanitizeFormPassword($_POST["newPassword"]);
    $newPassword2 = FormSanitizer::sanitizeFormPassword($_POST["newPassword2"]);

    if ($account->updatePassword($oldPassword, $newPassword, $newPassword2, $userLoggedInObj->getUsername())) {
        $passwordMessage = "<div class='alert alert-success'>
					<strong>Success!</strong> Password updated Successfully!
								</div>";
    } else {
        $errorMessage = $account->getFirstError();


        if ($errorMessage == "") $errorMessage = "Something went wrong";

        $passwordMessage = "<div class='alert alert-danger'>
					<strong>ERROR!</strong> $errorMessage
								</div>";
    }
}
if (isset($_POST["saveDetailsButton"])) {

    $account = new Account($con);

    /* $oldZipcode = ($_POST["oldZipcode"]);
     $newZipcode = ($_POST["newZipcode"]);*/


    /*if ($account->updateZipcode($oldZipCode, $newZipcode, $userLoggedInObj->getUsername())) {
        $zipcodeMessage = "<div class='alert alert-success'>
					<strong>Success!</strong> Zip Code updated Successfully!


								</div>";
    } else {
        $errorMessage = $account->getFirstError();

        if ($errorMessage == "") $errorMessage = "Something went wrong";

        $zipcodeMessage = "<div class='alert alert-danger'>
					<strong>ERROR!</strong> $errorMessage
								</div>";
    }*/
}

?>


    <div class="settingsContainer column">

        <div class="formSection">
            <?php echo $detailsMessage; ?>

            <?php
            echo $formProvider->createUserDetailsForm(
                isset($_POST["email"]) ? $_POST["email"] : $userLoggedInObj->getEmail(), $userLoggedInObj
            );
            ?>
        </div>

        <div class="formSection">
            <div class="message">
                <?php echo $passwordMessage; ?>

            </div>
            <?php
            echo $formProvider->createPasswordForm();
            ?>
        </div>


    </div>

<?php
require_once("includes/footer.php");

?>