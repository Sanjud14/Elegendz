<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/includes/classes/User.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/includes/classes/Notification.php");
$usernameLoggedIn = User::isLoggedIn() ? $_SESSION["userLoggedIn"] : "";
$userLoggedInObj = User::isLoggedIn() ? new User($con, $usernameLoggedIn) : null;
$notifications = Notification::retrieveUserNotifications($con, $userLoggedInObj->getId(), false, 3);
$userRole = $userLoggedInObj->getRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo ucfirst($section) ?>
        / <?php if ($userRole == User::ROLE_SPONSOR) echo 'Sponsor Panel'; else echo 'Admin' ?> / E Legendz</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/_favicon.php') ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">

    <!-- Libraries Stylesheet -->
    <!-- <link rel="stylesheet" href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css"/> -->

    <!-- Customized Bootstrap Stylesheet -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous">
    <link href="/assets/css/bootstrap-suggest.css">

    <!-- Template Stylesheet -->
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/banner.css">
    <script src="https://unpkg.com/vue@3.2.26/dist/vue.global.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="/assets/js/bootstrap-suggest.min.js"></script>
    <script src="/assets/js/moment.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/assets/js/jscolor.js"></script>
</head>

<body>
<div class="container-xxl position-relative bg-white d-flex p-0">
    <!-- Spinner Start -->
    <div id="spinner"
         class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


    <!-- Sidebar Start -->
    <div class="sidebar pe-4 pb-3">
        <nav class="navbar bg-light navbar-light">
            <a href="/" class="navbar-brand mx-4 mb-3">
                <h3 class="text-primary text-center"><img src="/assets/images/weblogo-small2.png"/></h3>
            </a>
            <div class="d-flex align-items-center ms-4 mb-4">
                <div class="position-relative">
                    <img class="rounded-circle" src="<?php echo $userLoggedInObj->getProfilePictureFullPath() ?>" alt=""
                         style="width: 40px; height: 40px;">
                    <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0"><?php echo $userLoggedInObj->getUsername() ?></h6>
                    <span><?php if ($userRole == User::ROLE_SPONSOR) echo 'Sponsor Panel'; else echo 'Admin' ?></span>
                </div>
            </div>
            <div class="navbar-nav w-100">
                <?php if ($userRole == User::ROLE_SPONSOR) { ?>
                    <a href="/sponsor/status"
                       class="nav-item nav-link <?php if ($section == 'status') echo 'active'; ?>"><i
                                class="bi bi-calendar-range me-2"></i>Status</a>
                    <a href="/sponsor/business-data"
                       class="nav-item nav-link <?php if ($section == 'business_data') echo 'active'; ?>"><i
                                class="bi bi-layout-text-window-reverse me-2"></i>Edit Banner</a>
                <?php } else { ?>
                    <a href="/admin/news" class="nav-item nav-link <?php if ($section == 'news') echo 'active'; ?>"><i
                                class="bi bi-newspaper"></i>News Blog</a>
                <?php } ?>

            </div>
        </nav>
    </div>
    <!-- Sidebar End -->


    <!-- Content Start -->
    <div class="content">
        <!-- Navbar Start -->
        <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
            <a href="/" class="navbar-brand d-flex d-lg-none me-3">
                <h2 class="text-primary mb-0"><img src="/assets/images/weblogo-small2.png" id="logo_small"/></h2>
            </a>
            <a href="#" class="sidebar-toggler flex-shrink-0">
                <i class="fa fa-bars"></i>
            </a>
            <form class="d-none d-md-flex ms-4" action="/search" id="search_form" method="GET">
                <input class="form-control border-0" type="search" placeholder="Search" name="term"
                       onkeydown="search(this)">
            </form>
            <div class="navbar-nav align-items-center ms-auto">

                <div class="nav-item dropdown" id="notifications_menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-bell me-lg-2"></i>
                        <span class="d-none d-lg-inline-flex">Notifications</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <?php foreach ($notifications as $notification) {
                            $titleNoTags = strip_tags($notification['title']);
                            $notificationTitle = strlen($titleNoTags) > 35 ? substr($titleNoTags, 0, 35) . "..." : $titleNoTags;
                            ?>
                            <a href="/user/notifications" class="dropdown-item">
                                <h6 class="fw-normal mb-0"><?php echo $notificationTitle ?></h6>
                                <small><?php echo date('F jS, Y h:i', strtotime($notification['created_at'])) ?></small>
                            </a>
                            <hr class="dropdown-divider">
                        <?php } ?>
                        <a href="/user/notifications" class="dropdown-item text-center">See all notifications</a>
                    </div>
                </div>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img class="rounded-circle me-lg-2"
                             src="<?php echo $userLoggedInObj->getProfilePictureFullPath() ?>" alt=""
                             style="width: 40px; height: 40px;">
                        <span class="d-none d-lg-inline-flex"><?php echo $userLoggedInObj->getUsername() ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="/<?php echo $userLoggedInObj->getUsername() ?>" class="dropdown-item">My Profile</a>
                        <a href="/logout" class="dropdown-item">Log Out</a>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Navbar End -->




