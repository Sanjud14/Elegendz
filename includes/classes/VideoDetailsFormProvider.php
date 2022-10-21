<?php

class VideoDetailsFormProvider
{

    private $con;

    public function __construct($con)
    {
        $this->con = $con;
    }

    public function createUploadForm()
    {
        $fileInput = $this->createFileInput();
        $titleInput = $this->createTitleInput();
        // $descriptionInput = $this->createDescriptionInput();
        //  $privacyInput = $this->createPrivacyInput();
        $categoriesInput = $this->createCategoriesInput();
        $copyrightCheck = $this->createCopyrightCheckbox();
        $uploadButton = $this->createUploadButton();
        $youtubeInput = $this->createYoutubeSoundcloudInput();
        $youtubeId = $this->createYoutubeId();
        $youtubeDuration = $this->createYoutubeDuration();
        $inputType = $this->createInputType();
        // $artistNameInput = $this->createArtistNameInput();
        $featuredUsersInputs = $this->createFeaturedUsersInputs(true);
        // $soundCloudInput = $this->createSoundCloudInput();
        $soundCloudThumbailInput = $this->createSoundCloudThumbnail();
        $audioFileInput = $this->createAudioFileInput(null);
        return "<form action='/processing.php' method='POST'  enctype='multipart/form-data' @submit='checkForm'>
                    <p class='mb-2'><b>Choose how to upload your video:</b></p>
                    $inputType
                    $fileInput
                    $youtubeInput
                    $youtubeId
                    $youtubeDuration
                    $soundCloudThumbailInput
                    $titleInput
                    $categoriesInput
                    $featuredUsersInputs
                    $audioFileInput
                    $copyrightCheck
                    $uploadButton
                </form>";
    }

    private function createFileInput()
    {

        return "<div class='mb-2'>
<div class='form-check'>
  <input class='form-check-input' type='radio' id='file_option' value='file' v-model='uploadType' name='type' required>
  <label class='form-check-label form-label' for='file_option'>
    Upload as a video file
    </label>
</div>
                    <transition name='fade'>
                    <input v-if='uploadType == \"file\"' type='file' class='form-control' id='file_input' name='fileInput' required>
                    </transition>
                    
                </div>";
    }

    private function createTitleInput($value = null)
    {
        if ($value)
            $valueHtml = "value = \"$value\"";
        else
            $valueHtml = "";
        return "<div class='mb-2 ' v-show=\"uploadType != 'youtube'\">
                    <label for='title_input' class='form-label'>Entertainer Name & Video Title</label>
                    <div class='emoji-picker-container'>
                    <input class='form-control' type='text'  name='titleInput' id='title_input' $valueHtml data-emojiable='true' v-model='title'  required>
                    </div>
                </div>";
    }

    /*private function createDescriptionInput()
    {
        return "<div class='mb-2'>
                    <label for='description_input' class='form-label'>Description</label>
                    <textarea class='form-control'  name='descriptionInput' id='description_input' rows='3' ></textarea>
                </div>";
    }

    private function createPrivacyInput()
    {
        return "<div class='mb-2'>
                    <label for='privacy_input' class='form-label'>Privacy</label>
                    <select class='form-select' name='privacyInput' id='privacy_input'>
                        <option value='0'>Private</option>
                        <option value='1'>Public</option>
                    </select>
                </div>";
    }*/

    public function createCategoriesInput($selectedId = null)
    {
        $query = $this->con->prepare("SELECT * FROM categories");
        $query->execute();

        $html = "<div class='mb-2'>
                    <label for='category_input' class='form-label'>Category</label>
                    <select class='form-select' name='categoryInput' id='category_input'>";

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $id = $row["id"];
            $name = $row["name"];

            if ($selectedId == $id)
                $selectedHtml = " selected ";
            else
                $selectedHtml = "";
            $html .= "<option value='$id' $selectedHtml>$name</option>";
        }

        $html .= "</select>
                </div>";

        return $html;

    }

    private function createCopyrightCheckbox()
    {
        return "<div class='mb-2'>
        <div class='form-check'>
  <input class='form-check-input' type='checkbox' value='1' id='copyright_check' v-model='copyrightCheck' autocomplete='off'>
  <label class='form-check-label' for='copyright_check'>
    I have the rights to this content
    </label>
</div>
</div>";
    }

    private function createYoutubeSoundcloudInput()
    {
        return "<div class='mb-2'>
<div class='form-check'>
  <input class='form-check-input' type='radio' name='type' id='youtube_option' value='youtube' v-model='uploadType' required>
  <label class='form-check-label form-label' for='youtube_option'>
    Enter the Youtube/SoundCloud link of your video
    </label>
</div>
                    <transition name='fade'>
                    <input v-if='uploadType == \"youtube\"' type='text' class='form-control' id='youtube_input' name='youtubeInput' v-model='youtubeUrl' required>
                    </transition>
                 
                   </div>
                   <transition name='fade'>
                   <div class='mb-2' id='youtube_preview' v-if='youtubeId'>
                   <iframe width='auto' height='240' v-bind:src=\"'https://www.youtube.com/embed/'+youtubeId\" title='YouTube video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>
                    </div>
                    </transition>
                        <transition name='fade'>
                 <div class='container-fluid' v-if='soundCloudIframe'>
                   <div class='row'>
                      <div class='col-10 offset-1 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4'>
                          <div class='mb-2' id='soundcloud_preview' v-html='soundCloudIframe'>
                   
                          </div>
                      </div>
                    </div>
                 </div>
                 </transition>
                    ";
    }

    private function createYoutubeId()
    {
        return "                    <input type='hidden' class='' id='youtube_id' name='youtube_id' :disabled='uploadType != \"youtube\"' v-model='youtubeId' >              ";
    }

    private function createYoutubeDuration()
    {
        return "<input type='hidden' class='' id='youtube_duration' name='youtube_duration' :disabled='uploadType != \"youtube\"' v-model='duration' >              ";
    }

    private function createInputType()
    {
        return "<input type='hidden' class='' id='type' name='type'  v-model='uploadType' required>              ";
    }

    /*private function createArtistNameInput()
    {
        return "<div class='mb-2'>
                    <label for='artist_name_input' class='form-label'>Entertainer Name</label>
                    <input class='form-control' type='text' maxlength='127'  name='artist_name' id='artist_name_input' required>
                </div>";
    }*/

    private function createFeaturedUsersInputs($showDisplayButton, $featuredUsers = [], $producerUsers = [], $recordLabel = null)
    {
        if (sizeof($featuredUsers) > 0) {
            $featuredValueHtml = " value=\"";
            foreach ($featuredUsers as $i => $user)
                $featuredValueHtml .= ($i > 0 ? " " : "") . "@" . $user->getUsername();
            $featuredValueHtml .= "\" ";
        } else
            $featuredValueHtml = "";
        //same for producers
        if (sizeof($producerUsers) > 0) {
            $producersValueHtml = " value=\"";
            foreach ($producerUsers as $i => $user)
                $producersValueHtml .= ($i > 0 ? " " : "") . "@" . $user->getUsername();
            $producersValueHtml .= "\" ";
        } else
            $producersValueHtml = "";

        if ($recordLabel) {
            $recordLabelValueHtml = " value=\"@" . $recordLabel->getUsername() . "\" ";
        } else
            $recordLabelValueHtml = "";

        $displayButtonHtml = "";
        if ($showDisplayButton)
            $displayButtonHtml = "<div class='mb-2'>
                        <div class='row'>
                            <div class='col-12 col-sm-6'>
                            <button type='button' class='btn btn-warning btn-sm mt-1' @click='showFeatureInput = !showFeatureInput'><span v-if='!showFeatureInput'>Add</span><span v-else>Hide</span> featured users</button>
                            </div>
                        </div>
                    </div>";

        return "
                    $displayButtonHtml
                     <transition name='fade'>
                        <div class='mb-0' v-if='showFeatureInput'>
                            <div class='row'>
                                <div class='col-12 col-sm-6'>
                                    <label for='featured_users_input' class='form-label'>Featured Entertainer(s)</label>
                                    <input class='form-control' type='text' name='featured_users' id='featured_users_input' autocomplete='off' placeholder='Ex: @john_doe @jane_doe' pattern='(@[a-zA-Z0-9_]+\s*)*' $featuredValueHtml />
                                    <small>Enter user names starting with the '@' character</small>
                                </div>
                                <div class='col-12 col-sm-6'>
                                    <label for='producers_input' class='form-label'>Producer(s)</label>
                                    <input class='form-control' type='text' name='producers' id='producers_input' autocomplete='off'  placeholder='Ex: @john_doe @jane_doe' pattern='(@[a-zA-Z0-9_]+\s*)*' $producersValueHtml>
                                    <small>Enter user names starting with the '@' character</small>
                                </div>
                            </div>
                   
                        
                        <div class='row'>
                            <div class='col-12 col-sm-6'>
                        <label for='record_label_input' class='form-label'>Record Label (if music)</label>
                        <input class='form-control' type='text' name='record_label' id='record_label_input' autocomplete='off'  placeholder='Ex: @doe_music_group' pattern='@[a-zA-Z0-9_]+\s*' $recordLabelValueHtml>
            
                        <small>Enter one user name starting with the '@' character</small>
                        </div>
                        </div>
                         </div>
                      </transition>   
               ";
    }

    private function createUploadButton()
    {
        return "<button type='submit' class='btn btn-primary submit-button' name='uploadButton'>UPLOAD</button>";
    }

    private function createEditButton()
    {
        return "<button type='submit' class='btn btn-primary submit-button' name='updateButton'>UPDATE</button>";
    }

    public function createEditForm($videoId, $userLoggedInObj)
    {
        $video = new Video($this->con, $videoId, $userLoggedInObj);

        $titleInput = $this->createTitleInput($video->getTitle());

        $categoriesInput = $this->createCategoriesInput($video->getCategory());
        // $copyrightCheck = $this->createCopyrightCheckbox();
        $uploadButton = $this->createEditButton();
        $youtubeId = $this->createYoutubeId();
        $youtubeDuration = $this->createYoutubeDuration();

        $featuredUsers = $video->getUsersFeatures();
        $producerUsers = $video->getProducers();
        $recordLabelId = $video->getRecordLabel();

        if ($recordLabelId)
            $recordLabelUser = new User($this->con, null, $recordLabelId);
        else
            $recordLabelUser = null;

        $audioFileInputs = $this->createAudioFileInput($video->getAudioFilePath());

        $featuredUsersInputs = $this->createFeaturedUsersInputs(false, $featuredUsers, $producerUsers, $recordLabelUser);
        return "<form action='' method='POST'  enctype='multipart/form-data' @submit='checkForm'>
                    $youtubeId
                    $youtubeDuration
                    $titleInput
                    $featuredUsersInputs
                    $categoriesInput
                    $audioFileInputs
                    $uploadButton
                </form>";
    }


    private function createSoundCloudThumbnail()
    {
        return "<input type='hidden' class='' id='soundcloud_thumbnail' name='soundcloud_thumbnail' :disabled='uploadType != \"soundcloud\"' v-model='soundCloudThumbnail' > ";
    }

    private function createAudioFileInput($existingFile = null)
    {
        return "<div class='mb-2'>
                    <label for='audio_file_path' class='form-label'>" . ($existingFile ? "Replace" : "Add") . " the audio version of the song</label>
                    " . ($existingFile ? "<br/><figure>
    <audio
        controls
        src='" . $existingFile . "'>
            Your browser does not support the
            <code>audio</code> element.
    </audio>
</figure>" : "") . "
                    <input type='file' class='form-control' id='audio_file_path' name='audio_file_path' accept='.mp3,audio/*' ref='audioFile'>
                </div>";
    }

}

?>