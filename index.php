<?php
session_start();
// Require composer autoloader

require_once dirname(__FILE__) . '/includes/classes/User.php';
require_once dirname(__FILE__) . '/includes/classes/StripeHandler.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define routes
$router->get('/(index)?', function () {
    define("OG_TITLE", "E Legendz");
    define("OG_URL", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']);
    define("OG_TYPE", 'music.song');
    define("OG_DESCRIPTION", "The Music Streaming Platform for Indie Artists");
    define("OG_IMAGE", $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/elegendz/assets/images/weblogo_facebook5.png");
    require_once('trending.php');
});
$router->get('/trending', function () {
    require_once('trending.php');
});
$router->get('/legendz', function () {
    require_once('champions.php');
});
$router->get('/about', function () {
    require_once('About.php');
});
$router->get('/watch', function () {
    require_once('watch.php');
});
$router->get('/search', function () {
    require_once('search.php');
});
$router->get('/news', function () {
    require_once('news.php');
});
$router->match('GET|POST', '/sign-in', function () {
    require_once('signIn.php');
});
$router->match('GET|POST', '/sign-up', function () {
    require_once('signUp.php');
});

$router->match('GET|POST', '/sponsor-submit', function () {
    require_once('sponsors_submit.php');
});

/*$router->match('GET|POST', '/payments/paypal-webhook', function () {
    // require_once('sponsors_submit.php'); @TODO
});*/

/*$router->get( '/sign(*)?in', function() {
    require_once ('signIn.php');
});*/

//only logged in users:
$router->before('GET|POST', '/user/.*', function () {
    if (!isset($_SESSION['userLoggedIn'])) {
        header('location: /sign-in');
        exit();
    }
});
//users
$router->mount('/elegendz/user', function () use ($router) {
    $router->get('/upload', function () {
        require_once('upload.php');
    });
    $router->get('/elegendz/notifications', function () {
        require_once('notifications.php');
    });
});

//admin
$router->before('GET|POST', '/elegendz/admin/.*', function () {
    if (!isset($_SESSION['userLoggedIn'])) {
        header('location: /elegendz/sign-in');
        exit();
    }
    if (($_SESSION['userRole'] != User::ROLE_ADMIN)) {
        header('location: /elegendz/sign-in');
        exit();
    }
});

$router->mount('/elegendz/admin', function () use ($router) {

    $router->get('/elegendz/index', function () {
        header('location: /elegendz/admin/news');
        exit();
    });
    $router->get('/news(/index)?', function () {
        require_once('admin/news/index.php');
    });
    $router->match('GET|POST', '/news/create', function () {
        require_once('admin/news/edit.php');
    });
    $router->match('GET|POST', '/news/edit/(\d+)', function ($id) {
        require_once('admin/news/edit.php');
    });
    $router->post('/news/delete/(\d+)?', function ($id) {
        require_once('admin/news/delete.php');
    });
});

//sponsor
$router->before('GET|POST', '/sponsor/.*', function () {
    if (!isset($_SESSION['userLoggedIn'])) {
        header('location: /sign-in');
        exit();
    }
    if (($_SESSION['userRole'] != User::ROLE_SPONSOR)) {
        header('location: /sign-in');
        exit();
    }
});

$router->mount('/sponsor', function () use ($router) {
    $router->get('/index', function () {
        header('location: /sponsor/status');
        exit();
    });
    $router->match('GET|POST', '/status', function () {
        require_once('admin/sponsor/status.php');
    });
    $router->match('GET|POST', '/business-data', function () {
        require_once('admin/sponsor/business_data.php');
    });

});

//stripe
$router->post('/stripe-webhook', function () {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php');
    $stripeHandler = new StripeHandler($con, null);
    $stripeHandler->webhook();
});

$router->post('/create-checkout-session', function () {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php');
    $stripeHandler = new StripeHandler($con, null);
    $stripeHandler->preSubscribe();
});


$router->get('/logout', function () {
    require_once('logout.php');
});

$router->get('/complete-sign-up', function () {
    require_once('signUpWithGoogle.php');
});

//any other thing will be processed as a user
$router->match('GET|POST', '/{profileUsername}', function ($profileUsername) {
    require_once('profile.php');
});


// Run it!
$router->run();

