<?php
require_once("ProfileData.php");
require_once("Video.php");
require_once("Account.php");
require_once("Constants.php");
require_once("FormSanitizer.php");
require_once("SettingsFormProvider.php");

class ProfileGenerator
{

    private $con, $userLoggedInObj, $profileData, $selectedTab;

    public function __construct($con, $userLoggedInObj, $profileUsername)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
        $this->profileData = new ProfileData($con, $profileUsername);
    }

    public function create()
    {
        $profileUsername = $this->profileData->getProfileUsername();

        if (!$this->profileData->userExists()) {
            return "User does not exist";
        }

        $canUpdate = ($this->profileData->getProfileUserObj()->getId() == ($this->userLoggedInObj ? $this->userLoggedInObj->getId() : null));

        //  $coverPhotoSection = $this->createCoverPhotoSection();
        $headerSection = $this->createHeaderSection();
        $tabsSection = $this->createTabsSection($canUpdate);
        $contentSection = $this->createContentSection();
        return "<div class='profileContainer'>
                    $headerSection
                    $tabsSection
                    $contentSection
                </div>";
    }

    public function createCoverPhotoSection()
    {
        //   $coverPhotoSrc = $this->profileData->getCoverPhoto();
        $name = $this->profileData->getProfileUserFullName();
        return "<div class='coverPhotoContainer'>
               
                    <span class='channelName'>$name</span>
                </div>";
    }

    public function createHeaderSection()
    {
        $profileImage = $this->profileData->getProfilePic();
        $name = $this->profileData->getProfileUserFullName();
        $subCount = $this->profileData->getSubscriberCount();

        $button = $this->createHeaderButton();

        return "<div class='profileHeader'>
                    <div class='userInfoContainer'>
                        <img class='profileImage' src='/elegendz/$profileImage'>
                        <div class='userInfo'>
                            <span class='title'>$name</span>
                            <span class='subscriberCount'>$subCount Subscribers</span>
                        </div>
                    </div>

                    <div class='buttonContainer'>
                        <div class='buttonItem'>    
                            $button
                        </div>
                    </div>
                </div>";
    }


//I have updated the tabs section
    public function createTabsSection($canUpdate)
    {
        $userFeaturedVideos = Video::getFeaturedVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $userProducedVideos = Video::getProducedVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $userAsRecordLabelVideos = Video::getRecordLabelVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $userChampionVideos = Video::getUserChampionVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        return "<ul class='nav nav-pills' id='profile_pills_tab' role='tablist'>" .
            ($canUpdate ? "
                     <li class='nav-item' role='presentation'>
                    <a class='nav-link active' id='about-tab' data-bs-toggle='pill' data-bs-target='#about' role='tab' 
                        aria-controls='about' aria-selected='true' href='#about' onclick='window.location.hash = \"#about\"'>About</a>
                    </li>" : "")
            . "<li class='nav-item' role='presentation'>
                    <a class='nav-link " . ($canUpdate ? '' : ' active ') . "' id='videos-tab' data-bs-toggle='pill' href='#videos' onclick='window.location.hash = \"#videos\"'
                        data-bs-target='#videos' role='tab' aria-controls='videos' aria-selected='false' >Songs</a>
                    </li>
                    " . (sizeof($userFeaturedVideos) > 0 ?
                "<li class='nav-item' role='presentation'>
                    <a class='nav-link' id='featured-tab' data-bs-toggle='pill' data-bs-target='#featured_songs' role='tab' 
                        aria-controls='featured_songs' aria-selected='false' href='#featured_songs' onclick='window.location.hash = \"#featured_songs\"'>Featured in</a>
                    </li>" : "") .
            (sizeof($userProducedVideos) > 0 ?
                "<li class='nav-item' role='presentation'>
                    <a class='nav-link' id='produced-tab' data-bs-toggle='pill' data-bs-target='#produced' role='tab' 
                        aria-controls='produced' aria-selected='false' href='#produced' onclick='window.location.hash = \"#produced\"'>Produced</a>
                    </li>" : "") .
            (sizeof($userAsRecordLabelVideos) > 0 ?
                "<li class='nav-item' role='presentation'>
                    <a class='nav-link' id='about-tab' data-bs-toggle='pill' data-bs-target='#record_label' role='tab' 
                        aria-controls='produced' aria-selected='false' href='#record_label' onclick='window.location.hash = \"#record_label\"'>Record Label</a>
                    </li>" : "") .
            (sizeof($userChampionVideos) > 0 ?
                "<li class='nav-item' role='presentation'>
                    <a class='nav-link' id='trophies-tab' data-bs-toggle='pill' data-bs-target='#trophies' role='tab' 
                        aria-controls='trophies' aria-selected='false' href='#trophies' onclick='window.location.hash = \"#trophies\"'><i class='bi bi-trophy-fill'></i> Trophies</a>
                    </li>" : "") .
            ($canUpdate ? "
                    <li class='nav-item' role='presentation'>
                    <a class='nav-link' id='about-tab' data-bs-toggle='pill' data-bs-target='#password' role='tab' 
                        aria-controls='password' aria-selected='false' href='#password' onclick='window.location.hash = \"#password\"'>Password</a>
                    </li>
                    <li class='nav-item' role='presentation'>
                    <a class='nav-link' id='about-tab' data-bs-toggle='pill' data-bs-target='#categories' role='tab' 
                        aria-controls='categories' aria-selected='false' href='#categories' onclick='window.location.hash = \"#categories\"'>Categories</a>
                    </li>

                    " : "") .
            "</ul>";
    }

//I copied this section to make createFeaturedSection function
    public function createContentSection()
    {

        $videos = $this->profileData->getUsersVideos();
        $userFeaturedVideos = Video::getFeaturedVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $userProducedVideos = Video::getProducedVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $userAsRecordLabelVideos = Video::getRecordLabelVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $userChampionVideos = Video::getUserChampionVideos($this->con, $this->profileData->getProfileUserObj()->getId(), $this->userLoggedInObj);
        $canUpdate = ($this->profileData->getProfileUserObj()->getId() == ($this->userLoggedInObj ? $this->userLoggedInObj->getId() : null));
        $activeTab = "videos";

        if (sizeof($videos) > 0) {
            $videoGrid = new VideoGrid($this->con, $this->userLoggedInObj);
            $videoGridHtml = $videoGrid->create($videos, null, false);
        } else {
            $videoGridHtml = "<p class='mt-3'>No songs yet</p>";
        }

        if (sizeof($userFeaturedVideos) > 0) {
            $videoGrid = new VideoGrid($this->con, $this->userLoggedInObj);
            $videoFeaturedGridHtml = $videoGrid->create($userFeaturedVideos, null, false);
        } else {
            $videoFeaturedGridHtml = "<p class='mt-3'>Not featured in any song yet.</p>";
        }

        if (sizeof($userProducedVideos) > 0) {
            $videoGrid = new VideoGrid($this->con, $this->userLoggedInObj);
            $videoProducedGridHtml = $videoGrid->create($userProducedVideos, null, false);
        } else {
            $videoProducedGridHtml = "<p class='mt-3'>No produced songs yet.</p>";
        }

        if (sizeof($userAsRecordLabelVideos) > 0) {
            $videoGrid = new VideoGrid($this->con, $this->userLoggedInObj);
            $videoRecordLabelGridHtml = $videoGrid->create($userAsRecordLabelVideos, null, false);
        } else {
            $videoRecordLabelGridHtml = "<p class='mt-3'>No songs with this user as record label yet.</p>";
        }

        if (sizeof($userChampionVideos) > 0) {
            $videoGrid = new VideoGrid($this->con, $this->userLoggedInObj);
            $trophiesGridHtml = $videoGrid->create($userChampionVideos, null, false, false, true);
        } else {
            $trophiesGridHtml = "<p class='mt-3'></p>";
        }


        $account = new Account($this->con);

        $alertHtml = "";
        $categoriesAlertHtml = "";
        $passwordAlertHtml = "";
        if ($canUpdate) {
            $settingsFormProvider = new SettingsFormProvider($this->con);

            if (isset($_POST["submitButton"])) {//update profile

                $email = FormSanitizer::sanitizeFormEmail($_POST["email"]);

                $zipCode = FormSanitizer::sanitizeZipcode($_POST['zipcode']);

                $allowEmailMessages = (isset($_POST['allow_email_messages']) && $_POST['allow_email_messages'] == 1) ? 1 : 0;

                $wasSuccessful = $account->update($this->userLoggedInObj, $email, $zipCode, $allowEmailMessages);

                if ($wasSuccessful) {
                    $alertHtml = "<div class='row'><div class='col'><div class='alert alert-dismissable alert-success mt-3 fade show rounded-0' role='alert'>Your profile was updated <button type='button' class='btn-close float-end' data-bs-dismiss='alert' aria-label='Close'></div>  </div></div>";
                    //refresh user
                    $this->userLoggedInObj = new User($this->con, null, $this->userLoggedInObj->getId());
                }

                $activeTab = 'about';

            }

            if (isset($_POST["savePasswordButton"])) {//update password

                $account = new Account($this->con);

                $oldPassword = FormSanitizer::sanitizeFormPassword($_POST["oldPassword"]);
                $newPassword = FormSanitizer::sanitizeFormPassword($_POST["newPassword"]);
                $newPassword2 = FormSanitizer::sanitizeFormPassword($_POST["newPassword2"]);

                if ($account->updatePassword($oldPassword, $newPassword, $newPassword2, $this->userLoggedInObj->getUsername())) {
                    $passwordAlertHtml = "<div class='row'><div class='col'><div class='alert alert-dismissable alert-success mt-3 fade show rounded-0' role='alert'>Password updated succesfully<button type='button' class='btn-close float-end' data-bs-dismiss='alert' aria-label='Close'></div>  </div></div>";
                } else {
                    $errorMessage = $account->getFirstError();

                    if ($errorMessage == "") $errorMessage = "Something went wrong";

                    $passwordAlertHtml = "<div class='row'><div class='col'><div class='alert alert-danger mt-2'>
					<strong>ERROR!</strong> $errorMessage
								</div></div></div>";
                }
            }

            if (isset($_POST["submitCategories"])) {//update categories

                $query = $this->con->prepare("SELECT * FROM categories ORDER BY id ASC");
                $query->execute();
                $categories = $query->fetchAll();
                $userCategories = [];
                foreach ($categories as $category) {
                    if (isset($_POST[$category['id']]) && $_POST[$category['id']] == 1) {//add relation
                        $userCategories[] = $category['id'];
                    }
                }
                $this->userLoggedInObj->saveUserCategories($userCategories);

                /*   $_SESSION['message_display'] = "Your categories were updated";
                   $_SESSION['message_display_type'] = "warning";*/

                if (/*$wasSuccessful*/ true) {

                    $categoriesAlertHtml = "<div class='row'><div class='col'><div class='alert alert-dismissable alert-success mt-3 fade show rounded-0' role='alert'>Your categories were updated <button type='button' class='btn-close float-end' data-bs-dismiss='alert' aria-label='Close'></div>  </div></div>";
                }
                $activeTab = 'categories';
            }

            //update form
            $aboutHtml = " <div class='container-fluid'>
                    $alertHtml
                    
                    <div class='row'>
                    <div class='col-12 col-md-10 col-lg-8'>
                        <div class='window-box'>
                        <form method='POST' enctype='multipart/form-data'>
                        <div class='mb-2'>
                        <label for='email' class='form-label w-100'>Email</label>
              
                        <input type='email' name='email' id='email' placeholder='Email' autocomplete='off' class='form-control'
                               value='" . (isset($_POST['email']) ? $_POST['email'] : $this->userLoggedInObj->getEmail()) . "' required>
                                         " . $account->getError(Constants::$emailsDoNotMatch) . "
                        " . $account->getError(Constants::$emailInvalid) . "
                        " . $account->getError(Constants::$emailTaken) . "
                               </div>
                        <div class='mb-2'>
                         <label for='zipcode' class='form-label w-100'>Zip Code</label>
       
                        <input type='text' name='zipcode' id='zipcode' value='" . (isset($_POST['zipcode']) ? $_POST['zipcode'] : $this->userLoggedInObj->getZipcode()) . "' placeholder='Zip code'
                        class='form-control' required>
                                          " . $account->getError(Constants::$invalidZipcode) . "
                        </div>
                        <div class='mb-2'>
                        <label for='profile_picture' class='form-label w-100'>Update Profile Picture (optional)</label> 
         
                        <input type='file' name='profile_picture' id='profile_picture'  class='form-control'>
                                       " . $account->getError(Constants::$uploadFailed) . "
                        " . $account->getError(Constants::$unrecognizedImageType) . "
                        " . $account->getError(Constants::$invalidImageType) . "
                        " . $account->getError(Constants::$imageTooBig) . "
                        </div>
                        <div class='mb-2'>
                        
                        <div class='form-check'>
                            <input type='checkbox' name='allow_email_messages' id='allow_email_messages' autocomplete='off'
                             class='form-check-input' value='1' " . (($this->userLoggedInObj->getAllowEmailMessages() == 1) ? 'checked' : '') . ">
                            <label for='allow_email_messages' class='form-check-label'>Allow other users to e-mail me</label> 
                        </div>
                        </div>
                         <div class='mt-3'>
                        <input type='submit' name='submitButton' class='btn btn-warning' value='UPDATE'>
                        </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    </div>";

            //password form
            $passwordForm = $settingsFormProvider->createPasswordForm($account);
            $passwordHtml = "<div class='container-fluid'>
            $passwordAlertHtml
                    <div class='row'>
                    <div class='col-12 col-md-12 col-lg-10'>
                        <div class='window-box'>
                      $passwordForm
                        </div>
                    </div>
                    </div>
                    </div>";

            $categoriesInput = $settingsFormProvider->createCategoriesInput($this->userLoggedInObj);
            $categoriesHtml = "<div class='container-fluid'>
            $categoriesAlertHtml
                    <div class='row'>
                    <div class='col-12 col-md-12 col-lg-10'>
                        <div class='window-box'>
                                 <form method='POST' >
                        $categoriesInput
                        <div class='mt-3'>
                        <input type='submit' name='submitCategories' class='btn btn-warning' value='UPDATE'>
                        </div>
                         </form>
                        </div>
                    </div>
                    </div>
                    </div>";
        } else {//show profile
            $aboutHtml = "";
            $categoriesHtml = "";
            $passwordHtml = "";
        }


        return "<div class='tab-content channelContent' id='pills-tabContent'>
                     <div class='tab-pane fade " . ($canUpdate ? ' show active' : '') . " ' id='about' role='tabpanel' aria-labelledby='about-tab'>
                        $aboutHtml
                    </div>
                    <div class='tab-pane fade " . ($canUpdate ? '' : ' show active ') . "'   id='videos' role='tabpanel' aria-labelledby='videos-tab'>
                        $videoGridHtml
                    </div>
                     <div class='tab-pane fade'  id='featured_songs' role='tabpanel' aria-labelledby='featured-tab'>
                        $videoFeaturedGridHtml
                    </div>
                     <div class='tab-pane fade ' id='produced' role='tabpanel' aria-labelledby='produced-tab'>
                        $videoProducedGridHtml
                    </div>
                    <div class='tab-pane fade ' id='record_label' role='tabpanel' aria-labelledby='record-label-tab'>
                        $videoRecordLabelGridHtml
                    </div>
                    <div class='tab-pane fade ' id='trophies' role='tabpanel' aria-labelledby='record-label-tab'>
                        $trophiesGridHtml
                    </div>
                    <div class='tab-pane fade ' id='password' role='tabpanel' aria-labelledby='password-tab'>
                        $passwordHtml
                    </div>
                      <div class='tab-pane fade ' id='categories' role='tabpanel' aria-labelledby='about-tab'>
                        $categoriesHtml
                    </div>
                </div>";
    }

    //Here I made for featured videos I don't think this is is correct because of the videoGridHtml

    public function createFeaturedSection()
    {

        $videos = $this->profileData->getUsersFeaturedVideos();

        if (sizeof($videos) > 0) {
            $videoFeaturesGrid = new VideoGrid($this->con, $this->userLoggedInObj);
            $videoGridHtml = $videoFeaturesGrid->create($videos, null, false);
        } else {
            $videoGridHtml = "<span>This user has no featured videos</span>";
        }

        return "<div class='tab-content channelContent'>
                    <div class='tab-pane fade show active' id='videos' role='tabpanel' aria-labelledby='videos-tab'>
                        $videoGridHtml
                    </div>
                    <div class='tab-pane fade' id='about' role='tabpanel' aria-labelledby='about-tab'>

                    </div>
                </div>";


        $aboutSection = $this->createAboutSection();

        return "<div class='tab-content channelContent'>
                    <div class='tab-pane fade show active' id='videos' role='tabpanel' aria-labelledby='videos-tab'>
                        $videoGridHtml
                    </div>
                    <div class='tab-pane fade' id='about' role='tabpanel' aria-labelledby='about-tab'>
                        $aboutSection
                    </div>
                </div>";
    }

    private function createHeaderButton()
    {
        if (!$this->userLoggedInObj || $this->userLoggedInObj->getUsername() == $this->profileData->getProfileUsername()) {
            return "";
        } else {
            return ButtonProvider::createSubscriberButton(
                $this->con,
                $this->profileData->getProfileUserObj(),
                $this->userLoggedInObj, $this->userLoggedInObj->getAllowEmailMessages());
        }
    }

    private function createAboutSection()
    {
        $html = "<div class='section'>
                    <div class='title'>
                        <span>Details</span>
                    </div>
                    <div class='values'>";

        $details = $this->profileData->getAllUserDetails();
        foreach ($details as $key => $value) {
            $html .= "<span>$key: $value</span>";
        }

        $html .= "</div></div>";

        return $html;
    }
}

?>