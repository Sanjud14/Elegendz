<?php
//get zip codes in array
$query = $con->prepare("SELECT DISTINCT(CONVERT(zipcode,SIGNED)) FROM zipcodes ");
$query->execute();
$zipcodes = $query->fetchAll(PDO::FETCH_COLUMN);
//var_dump($zipcodes);
?>
<div class="modal fade" id="zipcode_popup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Please enter your zipcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <span class="errorMessage" v-if="errorMessage">{{errorMessage}}</span>
                        <input type="text" class="form-control" id="zipcode" v-model="zipcode"
                               @keyup.enter="processZipcode">
                        <small>Your zipcode will help us to provide you customized content</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="skipZipcode">Not now</button>
                <button type="button" class="btn btn-primary" @click="processZipcode">Submit</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var zipcodeModal = new bootstrap.Modal(document.getElementById('zipcode_popup'), {
        keyboard: false
    })

    const zipcodePopup = {
        setup(props) {

            const zipcodes = [<?php foreach ($zipcodes as $zipcode) echo $zipcode . ',' ?>];
            const registeredZipcode = <?php echo(isset($_SESSION['zipcode']) ? "'" . $_SESSION['zipcode'] . "'" : 'null'); ?>;
            const zipcode = Vue.ref(null);
            const errorMessage = Vue.ref(null);

            console.log(registeredZipcode);
            if (!localStorage.getItem('zipcode') && registeredZipcode)
                localStorage.setItem('zipcode', registeredZipcode);

            let skippedZipcode = null;
            if (localStorage.getItem('skipped_zipcode'))
                skippedZipcode = JSON.parse(localStorage.getItem('skipped_zipcode'));
            if (!localStorage.getItem('zipcode') && (!skippedZipcode || skippedZipcode.value !== 1))
                zipcodeModal.show();

            const processZipcode = () => {
                errorMessage.value = null;
                console.log(zipcode.value);
                if (zipcodes.includes(parseInt(zipcode.value))) {
                    //success
                    localStorage.setItem('zipcode', zipcode.value);
                    zipcodeModal.hide();
                } else
                    errorMessage.value = "Invalid USA zip code";
            }

            const skipZipcode = () => {
                const now = new Date()
                let ttl = 1000 * 360 * 24 * 30;
                // `item` is an object which contains the original value
                // as well as the time when it's supposed to expire
                const item = {
                    value: 1,
                    expiry: now.getTime() + ttl,
                }
                localStorage.setItem('skipped_zipcode', JSON.stringify(item));
                zipcodeModal.hide();
            }


            return {
                processZipcode,
                zipcode,
                errorMessage,
                skipZipcode,
            }
        }
    }

    Vue.createApp(zipcodePopup, {}).mount('#zipcode_popup');

</script>