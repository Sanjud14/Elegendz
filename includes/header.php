<?php
require_once("includes/config.php");
require_once("includes/classes/ButtonProvider.php");
require_once("includes/classes/User.php");
require_once("includes/classes/Video.php");
require_once("includes/classes/VideoGrid.php");
require_once("includes/classes/SubscriptionsProvider.php");
require_once("includes/classes/NavigationMenuProvider.php");
require_once("includes/classes/BlogPost.php");


$usernameLoggedIn = User::isLoggedIn() ? $_SESSION["userLoggedIn"] : "";
$userLoggedInObj = User::isLoggedIn() ? new User($con, $usernameLoggedIn) : null;

if (strpos($_SERVER["REQUEST_URI"], '.js') == false && strpos($_SERVER["REQUEST_URI"], '.png') == false
    && strpos($_SERVER["REQUEST_URI"], '.ico') == false && strpos($_SERVER["REQUEST_URI"], '.jpg') == false)
    $_SESSION["originatingpage"] = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

if ($userLoggedInObj)
    $userNewNotifications = $userLoggedInObj->getNewNotificationsAmount();
else
    $userNewNotifications = 0;

?>
<!DOCTYPE html>
<html>
<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-GDZ0VHYSVC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-GDZ0VHYSVC');
    </script>
    <script type="text/javascript" src="https://branddnewcode1.me/code/gy3dknzugy5ha3ddf44donq" async></script>
    <title><?php echo(isset($pageTitle) ? ($pageTitle . " / ") : ""); ?>E Legendz</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous">
    <link href="/elegendz/assets/css/bootstrap-suggest.css">
    <link rel="stylesheet" type="text/css" href="/elegendz/assets/css/style.css?v=109">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="/elegendz/assets/css/emoji.css" rel="stylesheet">
    <link href="/elegendz/assets/css/banner.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"></script>
    <script src="https://unpkg.com/vue@3.2.26/dist/vue.global.js"></script>
    <script src="/assets/js/moment.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/elegendz/assets/js/bootstrap-suggest.min.js"></script>
    <script src="/elegendz/assets/js/commonActions.js"></script>
    <script src="/elegendz/assets/js/userActions.js"></script>

    <script src="/elegendz/assets/js/config.js"></script>
    <script src="/elegendz/assets/js/util.js"></script>
    <script src="/elegendz/assets/js/jquery.emojiarea.js"></script>
    <script src="/elegendz/assets/js/emoji-picker.js"></script>

    <?php require('_favicon.php') ?>

    <meta name="description" content="The Entertainment Streaming Platform for Independent Creators">
    <?php if (defined('OG_TITLE')) { ?>
        <meta property="og:title" content="<?php echo OG_TITLE; ?>"/> <?php } ?>
    <?php if (defined('OG_URL')) { ?>
        <meta property="og:url" content="<?php echo OG_URL; ?>"/> <?php } ?>
    <?php if (defined('OG_TYPE')) { ?>
        <meta property="og:type" content="<?php echo OG_TYPE; ?>"/> <?php } ?>
    <?php if (defined('OG_IMAGE')) { ?>
        <meta property="og:image" content="<?php echo OG_IMAGE; ?>"/> <?php } ?>
    <?php if (defined('OG_DESCRIPTION')) { ?>
        <meta property="og:description" content="<?php echo OG_DESCRIPTION; ?>"/> <?php } ?>

</head>
<body>

<div id="pageContainer">

    <div id="mastHeadContainer">
        <button class="navShowHide ">
            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" class="bi" fill="currentColor"
                 viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                      d="M2.5 11.5A.5.5 0 0 1 3 11h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 3h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"></path>
            </svg>
        </button>

        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-2 offset-sm-1 offset-lg-0 col-4 offset-3 text-center text-sm-start">
                    <a class="logoContainer " href="/index">
                        <img src="/elegendz/assets/images/weblogo-small2.png" class="img-fluid" title="logo" alt="Site logo">
                    </a>
                </div>
                <div class="col-lg-8 col-md-7 col-sm-7 d-flex d-sm-flex text-center px-0">
                    <div id="ad_banner">
                        <div v-html="bannerCode"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rightIcons d-flex align-items-center">
            <?php if ($userLoggedInObj) { ?>
                <a class="upload btn btn-default me-3" href="/user/upload"><span>Upload</span></a>
                
                <div id="notifications_icon" title="notifications">
                    <a href="/user/notifications">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($userNewNotifications > 0) { ?>
                            <span class="badge"><?php echo $userNewNotifications ?></span>
                        <?php } ?>
                    </a>
                </div>
            <?php } ?>
            <?php echo ButtonProvider::createUserProfileNavigationButton($con, ($userLoggedInObj ? $userLoggedInObj->getUsername(): null)); ?>
        </div>

    </div>

    <div id="sideNavContainer" style="display:none;">

        <?php
        $navigationProvider = new NavigationMenuProvider($con, $userLoggedInObj);
        echo $navigationProvider->create($userNewNotifications);
        ?>

    </div>


    <div id="mainSectionContainer">

        <div id="mainContentContainer" class="container">
            <div class="row">
                <div class="col-12">
                    <?php require_once('_message_display.php') ?>
                </div>
            </div>

            <script type="text/javascript">
                $(document).ready(function () {
                    window.emojiPicker = new EmojiPicker({
                        emojiable_selector: '[data-emojiable=true]',
                        assetsPath: '/elegendz/assets/images/emoji-picker',
                        popupButtonClasses: 'fa fa-smile-o'
                    });
                    window.emojiPicker.discover();
                })

                const adBanner = {
                    setup(props) {
                        const bannerCode = Vue.ref('<div class="spinner-border text-warning mt-3" role="status"><span class="visually-hidden">Loading...</span></div>');
                        const key = '<?php echo GOOGLE_GEO_CODE_API_KEY ?>';
                        let zipcode = null;

                        const retrieveAdBanner = (zipcode) => {
                            console.log(zipcode);
                            axios.get('/ajax/getAdBanner.php?zipcode=' + zipcode)
                                .then(function (response) {
                                    if (response.data.banner_code)
                                        bannerCode.value = response.data.banner_code;//'<img src="' + response.data.logo_url + '" class="img-fluid" title="' + response.data.name + '" alt="' + response.data.name + '"/>';
                                    else
                                        bannerCode.value = "<a href='/sponsor-submit' class='d-flex flex-wrap align-items-center'><img src='/elegendz/assets/images/advertise-here-480x60-bg.png' class='img-fluid'/></a>";

                                })
                                .catch(function (error) {
                                    // handle error
                                    console.log(error);
                                });
                        }

                        <?php if($userLoggedInObj){ ?>
                        zipcode = <?php echo($userLoggedInObj->getZipcode() ? '"' . $userLoggedInObj->getZipcode() . '"' : 'null') ?>;
                        <?php } ?>
                        if (!zipcode)
                            zipcode = localStorage.getItem('zipcode') ? localStorage.getItem('zipcode') : null;

                        if (!zipcode) {
                            //google maps geolocation
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition((position) => {
                                    console.log(position);
                                    const xhttp = new XMLHttpRequest();
                                    xhttp.onload = function () {
                                        let json = JSON.parse(this.response);
                                        json.results.forEach(item => {
                                            console.log(item);
                                            if (item.address_components) {
                                                item.address_components.forEach(item => {
                                                    console.log(item.types,item.long_name);
                                                    if (item.types[0] === 'postal_code') {
                                                        zipcode = item.long_name.replace(/\D/g, '');
                                                        retrieveAdBanner(zipcode);
                                                    }
                                                });
                                            }
                                        });
                                    };
                                    console.log(position);
                                    let latitude = position.coords.latitude;
                                    let longitude = position.coords.longitude;
                                    xhttp.open("GET", `https://maps.google.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${key}`);
                                    xhttp.send();
                                })
                            } else {//no geolocation and no user logged, use standard banner
                                //bannerCode.value = "<a href='/sponsor-submit' class='d-flex flex-wrap align-items-center'><img src='/assets/images/advertise-here-480x60-bg.png' class='img-fluid'/></a>";
                                retrieveAdBanner('NONE');
                            }

                        } else
                            retrieveAdBanner(zipcode);

                        return {
                            bannerCode,
                        }
                    }
                }

                Vue.createApp(adBanner, {}).mount('#ad_banner');
            </script>

