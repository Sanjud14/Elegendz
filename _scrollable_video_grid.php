<div id="scrollable_grid<?php if (isset($videoGridIndex)) echo '_' . $videoGridIndex; ?>">
    <div class='videoGrid container' ref='scrollComponent' id="videogrid_container">

        <div class='row' v-if="videos.length>0">
            <div v-bind:class="'videoGridItem col-sm-6 col-md-6 mt-3 col-lg-4 col-xl-3 ps-sm-0 pe-sm-3 '+(championsList?'champion-cell':'')"
                 v-for="(n,i)  in displayedIndex">
                <a v-bind:href="'/watch?id='+videos[i].id" class='song-link'>
                    <div class='thumbnail'>
                        <div v-if="championsList" class=""><i class="bi bi-trophy-fill"></i>
                            {{videos[i].category_name}}
                        </div>
                        <img v-bind:src="videos[i].thumbnail?videos[i].thumbnail:'/assets/images/icons/Trophyicon.png'"
                             class='img-fluid'>
                        <div class='duration'>
                            <span>{{videos[i].duration}}</span>
                        </div>
                    </div>
                    <div class='details'>
                        <h3 class='title'>{{videos[i].title}}</h3>
                        <span class='username'>{{videos[i].uploadedBy}} - {{videos[i].category_name}}</span>
                        <div class='stats' v-if="!championsList">
                            <span class='viewCount'>{{videos[i].views}} views - </span>
                            <span class='timeStamp'>{{formatDate(videos[i].uploadDate)}}</span>
                        </div>
                        <!--<span class='description'>{{condenseText(videos[i].description,50)}}</span> -->
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    const ScrollableGrid<?php if (isset($videoGridIndex)) echo '_' . $videoGridIndex;  ?> = {
        setup(props) {

            const videos = <?php echo json_encode($standardVideos) ?>;
            //   const extraVideos =  <?php echo json_encode($subscriptionsVideos) ?>;
            const displayedIndex = Vue.ref(24);
            const increaseVideos = Vue.ref(24);
            const scrollComponent = Vue.ref(null);
            const championsList = <?php if (isset($championsMode) && $championsMode) echo 'true'; else echo 'false' ?>;
            const handleScroll = (e) => {
                // console.log(e);
                let element = scrollComponent.value;
                //  console.log(element.getBoundingClientRect().bottom,window.innerHeight);
                if (element.getBoundingClientRect().bottom <= window.innerHeight) {
                    loadMoreSongs();
                }
            };

            Vue.onBeforeMount(() => {
                if (videos.length < displayedIndex.value)
                    displayedIndex.value = videos.length;
            })

            Vue.onMounted(() => {
                window.addEventListener("scroll", handleScroll);
            })

            Vue.onUnmounted(() => {
                window.removeEventListener("scroll", handleScroll)
            })

            const formatDate = (date) => {
                return moment(String(date)).format('D MMM YYYY')
            }
            const condenseText = (text, max) => {
                // console.log(text,max);
                return ((text.length > max) ? text.substring(0, max - 3) + "..." : text)
            }

            const loadMoreSongs = () => {
                if (videos.length > displayedIndex.value)
                    displayedIndex.value += increaseVideos.value;
                if (videos.length < displayedIndex.value)
                    displayedIndex.value = videos.length;
            }


            return {
                videos,/* extraVideos,*/
                displayedIndex,
                increaseVideos,
                formatDate,
                condenseText,
                scrollComponent,
                championsList
            }
        }
    }

    Vue.createApp(ScrollableGrid<?php if (isset($videoGridIndex)) echo '_' . $videoGridIndex;  ?>).mount('#scrollable_grid<?php if (isset($videoGridIndex)) echo '_' . $videoGridIndex;  ?>')
</script>