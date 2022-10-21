<?php
ob_start(); //Turns on output buffering
if (session_status() === PHP_SESSION_NONE)
    session_start();

date_default_timezone_set("America/New_York");

try {
    $con = new PDO("mysql:dbname=elegendz;host=localhost", "root", "");
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $query = $con->prepare("SET NAMES 'utf8mb4'");
    $query->execute();
    $query = $con->prepare("SET CHARACTER SET utf8mb4");
    $query->execute();
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$youtubeApiKey = 'AIzaSyBqjtJNAVDd9FDyy4FkunfZ1l_KMxtiqOc';
$cronjobKey = 'dtq2emstr3hrav7z';


require __DIR__ . '/../vendor/autoload.php';
$gClient = new Google\Client();
$gClient->setClientId('496071145330-gvcsos8sd35r7mjs97ne31u78eolu52k.apps.googleusercontent.com');
//$gClient->setClientId('891560637449-glq9m9vdh6ll3stgfnqmekn829smh564.apps.googleusercontent.com');
$gClient->setClientSecret('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');
//$gClient->setClientSecret('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');
$gClient->setApplicationName('Login with Google');
$gClient->setRedirectUri('https://elegendz.net/includes/classes/SigInWithGoogle.php');
$gClient->addScope('https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email');
$login_url = $gClient->createAuthUrl();

//STRIPE
/*define('STRIPE_SECRET_KEY', 'sk_test_51KpgxkKHEwIgJIlCw4Bmbk1kLotBoUco1kprVK3FAq1oF8MXGa15ePbiNASA2v1mPsE63TTAigmMWILbzWXpxQAu000vTgRJ9y');
define('STRIPE_PUBLIC_KEY', 'pk_test_51KpgxkKHEwIgJIlCqA3mfnr51PyjmozKmqWu6i0EfULx4ANg930YZj1KWYhuu6DKtp1EhpPdCWEKTstg2wyEKxeT005mbSMu7a');*/
define('STRIPE_SECRET_KEY', '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');
define('STRIPE_PUBLIC_KEY', 'pk_live_51KpgxkKHEwIgJIlCCHFRtxfZiErkmcyBnDa0NHoeas4QsRyYDCP5k5BX4hL8Q3hYLxC0Fep49CUMoDKKVagJEwS9000MWvO7tT');
//define('STRIPE_PRODUCT_ID', 'prod_LXcPP6OonwnZoT');
define('STRIPE_PRODUCT_ID', 'prod_LXcNDdPto8fNLM');
//define('STRIPE_PRICE_ID', 'price_1KqXAXKHEwIgJIlCy4NQZWQU');
define('STRIPE_PRICE_ID', 'price_1LCnd8KHEwIgJIlCJrCp3Mpg');
//define('STRIPE_ENDPOINT_SECRET','whsec_0vENTBLTKIxZfJFGHCg7FQOJO1R7hOLu');
define('STRIPE_ENDPOINT_SECRET', 'whsec_MiPl15RwW7AgY5a8H13oWmyeYEP5HmyB');
define('STRIPE_FREE_TRIAL_DAYS', 7);


define('GOOGLE_GEO_CODE_API_KEY', 'AIzaSyBnxhZAyDxUXdubfgqNYLU_RyDULDqmhpg');
?>

