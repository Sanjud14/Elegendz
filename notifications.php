<?php
$pageTitle = "Notifications";
require_once("includes/config.php");
require_once("includes/header.php");
require_once("includes/classes/Notification.php");
//requires logged in
if (!User::isLoggedIn())
    header("Location: /signIn.php");

if (isset($_GET['$comments']) && ($_GET['$likes']) && ($_GET['$subscriptions'])) {
//??
}
?>

<h1>Notifications</h1>
<div id="notifications_board">
    <div class="notification-ui_dd-content">
        <!--<div id="button_panel" class="mb-2">
            <button class="btn btn-warning" v-on:click="selectAll()">Select All</button> &nbsp;
            <button class="btn btn-danger">Delete Selected</button>
        </div> -->
        <div id="notifications_panel">
            <div class="form-check ">
                <input type="checkbox" class="form-check-input" id="select_all" @change="toggleSelected($event)"/>
                <label class="form-check-label" for="select_all">Select all</label>
            </div>
            <button class="btn btn-danger float-right btn-sm" id="delete_selected" @click="deleteSelected()">Delete
                Selected
            </button>
        </div>
        <div v-if="notifications.length == 0">
            <p class="text-white text-center notification-text">No notifications reported.</p>
        </div>
        <transition-group name="fade" tag="div">
            <div v-bind:class="'notification-list'+(notifications[i].already_read?'':'notification-list--unread')"
                 v-for="(n,i) in displayedIndex" :key="notifications[i].id">
                <div class="notification-list_content">
                    <div class="notification-check  text-center">
                        <div class="form-check ">
                            <input class="form-check-input" type="checkbox" value="1" id="flexCheckDefault"
                                   @change="updateSelected($event,i)"
                                   :key="notifications[i].id" :checked="notifications[i].selected">
                        </div>
                    </div>
                    <div class="notification-list_img" v-if="notifications[i].author_picture">
                        <a v-bind:href="'/username/'+notifications[i].author_username">
                            <img v-bind:src="notifications[i].author_picture"
                                 v-bind:alt="notifications[i].author_username">
                        </a>
                    </div>
                    <div class="notification-list_detail">
                        <p v-html="notifications[i].title"></p>
                        <p class="text-muted" v-if="notifications[i].body">{{notifications[i].body}}</p>
                        <p class="text-muted"><small>{{timeSince(notifications[i].created_at)}}</small></p>
                    </div>
                </div>
                <div class="notification-list_feature-img" v-if="notifications[i].image">
                    <a v-if="notifications[i].image_link" v-bind:href="notifications[i].image_link">
                        <img v-bind:src="notifications[i].image" alt="Feature image">
                    </a>
                    <img v-else v-bind:src="notifications[i].image" alt="Feature image">
                </div>
                <!--   <div class="delete-button">
                       <i class="bi bi-trash-fill"></i>
                   </div>-->
            </div>
        </transition-group>
        <div class="w-100 text-center mt-3">
            <button class="btn btn-warning " v-if="displayedIndex < notifications.length"
                    @click="loadMoreNotifications()">Load More
            </button>
        </div>
    </div>


</div>
<!--<script type="text/javascript" src="/assets/js/vue-timeago.js"></script> -->

<script type="text/javascript">
    const NotificationsBoard = {
        setup(props) {
            const notifications = Vue.reactive(<?php echo json_encode(Notification::retrieveUserNotifications($con, $userLoggedInObj->getId(), true)) ?>);
            const displayedIndex = Vue.ref(12);
            const increaseNotifications = Vue.ref(12);

            Vue.onBeforeMount(() => {
                if (notifications.length < displayedIndex.value)
                    displayedIndex.value = notifications.length;
                for (let i = 0; i < notifications.length; i++) {
                    notifications[i].selected = false;
                    if (notifications[i].image != null)
                        notifications[i].image = correctRelativeUrl(notifications[i].image);
                    if (notifications[i].author_picture != null)
                        notifications[i].author_picture = correctRelativeUrl(notifications[i].author_picture);
                }
            })

            const loadMoreNotifications = () => {
                if (notifications.length > displayedIndex.value)
                    displayedIndex.value += increaseNotifications.value;
                if (notifications.length < displayedIndex.value)
                    displayedIndex.value = notifications.length;
            }

            const timeSince = (dateString) => {

                let date = new Date(Date.parse(dateString.replace(/-/g, '/')));
                let seconds = Math.floor((new Date() - date) / 1000);
                let interval = seconds / 31536000;
                if (interval > 1) {
                    return Math.floor(interval) + " years ago";
                }
                interval = seconds / 2592000;
                if (interval > 1) {
                    return Math.floor(interval) + " months ago";
                }
                interval = seconds / 86400;
                if (interval > 1) {
                    return Math.floor(interval) + " days ago";
                }
                interval = seconds / 3600;
                if (interval > 1) {
                    return Math.floor(interval) + " hours ago";
                }
                interval = seconds / 60;
                if (interval > 1) {
                    return Math.floor(interval) + " minutes ago";
                }
                return Math.floor(seconds) + " seconds ago";
            }

            const updateSelected = (e, i) => {
                if (e.target.checked) {
                    notifications[i].selected = true;
                } else {
                    notifications[i].selected = false;
                }
            }

            const selectAll = () => {
                for (let i = 0; i < displayedIndex.value; i++) {
                    notifications[i].selected = true;
                }
            }

            const unselectAll = () => {
                for (let i = 0; i < displayedIndex.value; i++) {
                    notifications[i].selected = false;
                }
            }

            const toggleSelected = (e) => {
                console.log(e.target.checked);
                if (e.target.checked)
                    selectAll();
                else
                    unselectAll();
            }

            const deleteSelected = (e) => {
                let selectedIds = [];
                for (let i = 0; i < displayedIndex.value; i++) {
                    if (notifications[i].selected)
                        selectedIds.push(notifications[i].id);
                }
                if (selectedIds.length === 0)
                    alert("No notifications selected!");
                else {
                    axios.post('/includes/ajax/deleteNotifications.php', {notifications: selectedIds})
                        .then(function (response) {
                            console.log(response);
                            for (let i = (notifications.length - 1); i >= 0; i--) {
                                if (notifications[i].selected)
                                    notifications.splice(i, 1);
                            }
                        })
                        .catch(function (error) {
                            if (error.response.status === 401)
                                window.location = "/login"
                        })
                }
            }

            const correctRelativeUrl = (url) => {
                if (url.substring(0, 4) !== 'http' && url.substring(0, 1) !== '/')
                    return '/' + url;
                else
                    return url;
            }

            /*    const correctRelativeUrl = Vue.computed(() => {
                    return author.books.length > 0 ? 'Yes' : 'No'
                })*/

            return {
                notifications,
                displayedIndex,
                increaseNotifications,
                timeSince,
                updateSelected,
                selectAll,
                toggleSelected,
                deleteSelected,
                loadMoreNotifications
            }
        }


    }

    Vue.createApp(NotificationsBoard).mount('#notifications_board');


</script>
<?php require_once("includes/footer.php"); ?>
