<?php
require_once('includes/config.php');
require_once("includes/classes/VideoDetailsFormProvider.php");
require_once("includes/header.php");
require_once("includes/classes/SettingsFormProvider.php");

$id = $_GET['id'];

$user = new User($con, null, $id);

/*$query = $con->prepare("SELECT * FROM users WHERE username = :un");
$query->bindParam(":un", $_SESSION["userLoggedIn"]);
$query->execute();
$user = $query->fetchObject();*/

$query = $con->prepare("SELECT * FROM categories ORDER BY id ASC");
$query->execute();
$settingsFormProvider = new SettingsFormProvider($con);
$categories = $query->fetchAll();

if (!empty($_POST)) {
    // handle post data
    $userCategories = [];
    foreach ($categories as $category) {
        if (isset($_POST[$category['id']]) && $_POST[$category['id']] == 1) {//add relation
            $userCategories[] = $category['id'];
        }
    }
    $user->saveUserCategories($userCategories);

    if ($_SESSION['siginWithGoogle'] !== "true") {
        /* $_SESSION['message_display'] = "User succesfully created. A mail was sent to your account to confirm your address.";
         $_SESSION['message_display_type'] = "warning";*/
        //force login
        $_SESSION["userLoggedIn"] = $user->getUsername();
        $_SESSION["userRole"] = $user->getRole();
        $_SESSION["zipcode"] = $user->getZipcode();
    } else {
        $_SESSION["userLoggedIn"] = $user->getUsername();
        $_SESSION["userRole"] = $user->getRole();
        if ($user->isAdmin()) {
            header("Location: /admin/index");
        } else {
            if (isset($_SESSION["originatingpage"]))
                header("Location: https://" . $_SESSION["originatingpage"]);
            else
                header("Location: /index");
        }
    }
    header("Location: index");
}

$categoriesInputHtml = $settingsFormProvider->createCategoriesInput($user);

?>
<div class="window-box">

    <form method="post">
        <?php echo $categoriesInputHtml ?>

        <div class="w-100 text-center">
            <input type="submit" name="submitButton" class="submit-button" value="SUBMIT">
        </div>
    </form>
</div>
<?php require_once("includes/footer.php"); ?>




