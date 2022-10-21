<?php
require_once("includes/header.php");
require_once("includes/classes/VideoDetailsFormProvider.php");
require_once("includes/classes/User.php");
//requires logged in
/*if (!User::isLoggedIn())
    header("Location: /signIn.php");*/
?>


<div class="window-box" id='upload_form'>
    <h3 class="mb-2">Upload Your Entertainment Video</h3>
    <?php
    $formProvider = new VideoDetailsFormProvider($con);
    echo $formProvider->createUploadForm();
    ?>

</div>

<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModal" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                Please wait. This might take a while.
                <img src="assets/images/icons/loading-spinner.gif">
            </div>

        </div>
    </div>
</div>


<script type="text/javascript">

    var users = [
        <?php
        $users = User::getAllUsers($con);
        foreach ($users as $user) {
            echo " {username: '" . $user['username'] . "', state: '" . $user['state'] . "'},\n";
        }
        ?>
    ];

    const UploadForm = {
        setup(props) {
            const uploadType = Vue.ref(null);
            const youtubeUrl = Vue.ref(null);
            const youtubeId = Vue.ref(null);
            //    const soundCloudUrl = Vue.ref(null);
            const soundCloudIframe = Vue.ref(null);
            const soundCloudThumbnail = Vue.ref(null);
            const copyrightCheck = Vue.ref(false);
            const duration = Vue.ref(null);
            const value = Vue.ref('');
            const title = Vue.ref(null);
            const showFeatureInput = Vue.ref(false);
            const options = ['Select option', 'options', 'selected', 'mulitple', 'label', 'searchable', 'clearOnSelect', 'hideSelected', 'maxHeight', 'allowEmpty', 'showLabels', 'onChange', 'touched'];
            const audioFile = Vue.ref(null);

            Vue.onMounted(() => {
                /* Had to use a Jquery plugin as there is no npm */
                $('#featured_users_input,#producers_input,#record_label_input').suggest('@', {
                    data: users,
                    map: function (user) {
                        return {
                            value: user.username,
                            text: '<strong>' + user.username + '</strong> <small>' + user.state + '</small>',
                        }
                    }
                });
            })

            Vue.watch(youtubeUrl, (url, prevUrl) => {
                if (url.toLowerCase().includes("soundcloud"))
                    validateSoundCloudVideo(url);
                else
                    validateYoutubeVideo(url);
            })

            Vue.watch(uploadType, (type, prevType) => {
                //   validateYoutubeVideo(url);
                if (type !== 'youtube') {
                    youtubeId.value = null;
                    youtubeUrl.value = null;
                }
                if (type !== 'soundcloud') {
                    soundCloudIframe.value = null;
                    // soundCloudUrl.value = null;
                    soundCloudThumbnail.value = null;
                }
            })

            Vue.watch(audioFile, (file, prevFile) => {
                console.log('file uploaded');
            })

            /*  Vue.watch(soundCloudUrl, (url, prevUrl) => {
                  validateSoundCloudVideo(url);
              })*/

            const validateYoutubeVideo = (url) => {
                if (url == null || url === '') {
                    youtubeId.value = null;
                    return;//no validation
                }
                let id = youtube_parser(url);
                //console.log(id);
                axios.get('https://www.googleapis.com/youtube/v3/videos?part=contentDetails,snippet&id=' + id + '&key=<?php echo $youtubeApiKey; ?>')
                    .then(function (response) {
                        console.log(response);
                        // handle success
                        if (response.data.pageInfo && response.data.pageInfo.totalResults === 1) {
                            youtubeId.value = id;
                            duration.value = response.data.items[0].contentDetails.duration;
                            title.value = response.data.items[0].snippet.title;

                        } else {
                            //  alert("Youtube video not found!");
                            youtubeId.value = null;
                        }
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                        alert("Youtube video not found!");
                        youtubeId.value = null;
                    });
            }

            function youtube_parser(url) {
                let regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
                let match = url.match(regExp);
                return (match && match[7].length == 11) ? match[7] : false;
            }

            function checkForm(e) {
                if (copyrightCheck.value === false) {
                    e.preventDefault();
                    alert("You need to have the rights to this song.");
                }
                if (uploadType.value === "youtube" && youtubeId.value == null) {
                    e.preventDefault();
                    alert("Youtube video not found!");
                }
                if (uploadType.value === "soundcloud" && soundCloudIframe.value == null) {
                    e.preventDefault();
                    alert("SoundCloud track not found!");
                }

                const file = audioFile.value.files[0];

                if (file.size > 10 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Audio file is too big! (> 15MB)');
                }
            }

            const validateSoundCloudVideo = (url) => {
                if (url == null || url === '') {
                    soundCloudIframe.value = null;
                    soundCloudThumbnail.value = null;
                    return;//no validation
                }

                var formData = new FormData();

                formData.append("format", "json");
                formData.append("url", url);

                fetch('https://soundcloud.com/oembed', {
                    method: 'POST',
                    body: formData
                }).then(function (response) {
                    console.log(response.status);
                    if (response.status === 200) {
                        const obj = response.json().then(data => {
                            // do something with your data
                            //  console.log(data);
                            soundCloudIframe.value = data.html;
                            soundCloudThumbnail.value = data.thumbnail_url;
                            title.value = data.title;
                        });
                    } else {
                        soundCloudIframe.value = null;
                        soundCloudThumbnail.value = null;
                    }
                }).catch(error => function () {
                    console.log(error);
                    soundCloudIframe.value = null;
                    soundCloudThumbnail.value = null;
                    //       alert('soundcloud video not found!');
                });
            }

        /*    const validateAudioFile = () => {


                alert('File OK');
            }*/

            return {
                uploadType,
                youtubeUrl,
                youtubeId,
                checkForm,
                copyrightCheck,
                duration,
                value,
                options, /*featuredUsers, producers, recordLabel*/
                //  soundCloudUrl,
                soundCloudIframe,
                soundCloudThumbnail,
                title,
                showFeatureInput,
                audioFile,
            };
        }
    }

    const app = Vue.createApp(UploadForm).mount('#upload_form');
    // app.component('vue-multiselect', window.VueMultiselect.default);
</script>


<?php require_once("includes/footer.php"); ?>
