<?php
require_once("includes/config.php");
require_once("includes/classes/Account.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/FormSanitizer.php");
require_once("includes/log.php");

$account = new Account($con);

/*$headers="From: noreply@elegendz.net" . "\r\n" . "Reply-To: noreply@elegendz.net" . "\r\n" . "X-Mailer: PHP/" . phpversion();
mail('freelance.frivas@gmail.com', 'Elegendz submit', 'test',$headers);*/

if (isset($_GET['email'])) {
    wh_log(urldecode($_GET['email']));
}

if (isset($_POST["submitButton"])) {
    //wh_log(http_build_query($_POST));

    $username = FormSanitizer::sanitizeFormUsername($_POST["username"]);

    $email = FormSanitizer::sanitizeFormEmail($_POST["email"]);
    $email2 = FormSanitizer::sanitizeFormEmail($_POST["email2"]);

    $zipCode = FormSanitizer::sanitizeZipcode($_POST['zipcode']);
    $user = $account->registerWithGoogle($username, $email, $email2, "1234five", "1234five",$zipCode);

    if ($user) {
        wh_log('Submit successful');
        $_SESSION['siginWithGoogle'] = "true";
        header("Location: signUp2.php?id=" . $user->getId());
    } elseif ($account->getFirstError() == '') {
        wh_log('Submit failed with unrecognized error');
        echo "Unrecognized error";
        exit;
    } else {
        wh_log('Submit failed with first error: ' . $account->getFirstError());
        $error = $account->getFirstError();
        header("Location: signUpWithGoogle.php?email=$email&username=$username&error=$error");
    }

}

function getInputValue($name)
{
    if (isset($_GET[$name])) {
        echo $_GET[$name];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>EZ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require('./includes/_favicon.php') ?>
</head>
<body>


<div class="signInContainer m-3">

    <div class="column">
        <div class="container-fluid">

            <div class="col">
                <div class="header">
                    <h3>Complete Sign Up</h3>
                    <br>
                    <img src="/assets/images/icons/Trophyicon.png" title="logo" alt="Site logo">
                </div>

                <div class="loginForm">

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <?php echo $account->getError(Constants::$usernameCharacters); ?>
                        <?php echo $account->getError(Constants::$usernameTaken); ?>
                        <input type="text" name="username" placeholder="Username" autocomplete="off"
                               value="<?php getInputValue('username'); ?>" required>

                        <input type="hidden" name="email" placeholder="Email" autocomplete="off"
                               value="<?php getInputValue('email'); ?>" required>
                        <input type="hidden" name="email2" placeholder="Confirm email" autocomplete="off"
                               value="<?php getInputValue('email'); ?>" required>

                        <?php echo $account->getError(Constants::$invalidZipcode); ?>
                        <input id="zipcode" type="text" name="zipcode" value="<?php getInputValue('zipcode'); ?>"
                               placeholder="Zip code"
                               required>

                        <?php if(isset($_GET['error'])) echo "<span class='errorMessage'>".$_GET['error']."</span>" ?>
                        <input type="submit" name="submitButton" value="SUBMIT">

                    </form>

                </div>
            </div>

        </div>

    </div>
</div>


</body>
</html>
</html>
<script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position)=>{
                getZipCode(position.coords.latitude,position.coords.longitude);
                console.log(position.coords.latitude);
                console.log(position.coords.longitude);
                //getZipCode(-87.623177,41.881832);
            });
        } else {
            console.log("Geolocation is not supported by this browser.");
        }
    }
    function getZipCode(latitude, longitude) {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            let json = JSON.parse(this.response);
            json.results.forEach(item => {
                if (item.address_components) {
                    item.address_components.forEach(item => {
                        if (item.types[0] === 'postal_code') {
                            document.getElementById('zipcode').value = item.long_name
                        }
                    });
                }
            });
        };
        let key = '<?php echo GOOGLE_GEO_CODE_API_KEY ?>';
        xhttp.open("GET", `https://maps.google.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${key}`);
        xhttp.send();
    }
    getLocation();
</script>