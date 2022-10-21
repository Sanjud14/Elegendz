<?php
$section = "status";
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/Sponsor.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/User.php');
$sponsor = new Sponsor($con, $userLoggedInObj->getSponsorId(), $userLoggedInObj);
$subscription = $sponsor->getCurrentlyActiveSubscription();
$subscriptionCities = [];
$lastInactiveSubscriptionCities = [];
$subscriptionZipcodesQty = 0;
if ($subscription) {
    $subscriptionCities = $subscription->getCities();
    foreach ($subscriptionCities as $city) {
        $subscriptionZipcodesQty += $city['zipcodes'];
    }
} else {
    $lastInactiveSubscription = $sponsor->getLastInactiveSubscription();
    if ($lastInactiveSubscription)
        $lastInactiveSubscriptionCities = $lastInactiveSubscription->getCities();
}
//get zip codes in array
/*$query = $con->prepare("SELECT COUNT(zipcode) AS zipcodes,city,state, county FROM zipcodes GROUP BY city");
$query->execute();
$zipcodes = $query->fetchAll(PDO::FETCH_ASSOC);*/
//get all states
$query = $con->prepare("SELECT * FROM states ORDER BY state ASC");
$query->execute();
$states = $query->fetchAll(PDO::FETCH_ASSOC);
//get zip codes already taken
$query = $con->prepare("SELECT zipcodes.city, zipcodes.state, zipcodes.county FROM sponsors_subscriptions_cities AS ssc INNER JOIN sponsors_subscriptions AS ss
ON ssc.subscription_id = ss.id INNER JOIN zipcodes ON zipcodes.city = ssc.city AND zipcodes.county = ssc.county AND zipcodes.state = ssc.state WHERE good_until IS NOT NULL AND good_until >= NOW() ORDER BY zipcodes.state, zipcodes.county, zipcodes.city ASC");
$query->execute();
$citiesTaken = $query->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['cancel'])) {
    $subscription->cancelStripeSubscription();
    //reload
    $subscription = $sponsor->getCurrentlyActiveSubscription();
    $_SESSION['message_display'] = 'You have unsubscribed from weekly payments. Your advertisement will still be displayed until the end of the period.';
    $_SESSION['message_display_type'] = 'success';
}

?>
<div class="container-fluid pt-4 px-4">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/_message_display.php'; ?>
    <div class="row g-4">
        <div class="col-sm-12 col-xl-12">
            <div class="bg-light rounded h-100 p-4">
                <h5 class="mb-4">Subscription Status</h5>
                <?php if (!$subscription) { ?>
                    <div class="alert alert-danger" role="alert">
                        You don't have an active subscription
                    </div>
                    <p>Please proceed to choose the USA cities that you want your advertisement to appear in </p>
                <?php } else { ?>
                    <div class="alert alert-success" role="alert">
                        Your weekly subscription is active. Last
                        billing: <?php echo date("F j, Y", strtotime($subscription->getLastBilling())); ?>. Will renew:
                        <b><?php echo $subscription->getWillRenew() ? 'yes' : 'no' ?></b>.
                    </div>
                <?php } ?>

                <div id="zipcodes_selector" class="">
                    <form>
                        <div class="row mb-3">
                            <div class="col-12 col-xl-3 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <!-- <input type="text" class="form-control" v-model="newZipcode" placeholder="New zipcode"
                                        pattern="[0-9]{5}" maxlength="5" autocomplete="off"/>-->
                                <select class="form-control" v-model="newState">
                                    <option :value="null" disabled>Select state</option>
                                    <option v-for="state in states" :value="state.code">{{state.name}}</option>
                                </select>
                            </div>
                            <div class="col-12 col-xl-3 col-lg-3 col-md-4 col-sm-6 mb-2">
                                <select class="form-control" v-model="newCounty" :disabled="newState == null">
                                    <option :value="null" disabled>Select county</option>
                                    <option v-for="county in selectedStateCounties" :value="county">{{county}}</option>
                                </select>
                            </div>
                            <div class="col-12 col-xl-3 col-lg-3 col-md-4 col-sm-6 mb-2 ">
                                <select class="form-control" v-model="newCity" :disabled="newCounty == null">
                                    <option :value="null" disabled>Select city</option>
                                    <option v-for="city in selectedCountyCities"
                                            :value="{city:city.name,zipcodes: city.zipcodes}">
                                        {{city.name}}
                                    </option>
                                </select>
                            </div>
                            <div class=" col-12 col-sm-6 col-lg-3 text-right">
                                <button :class="'btn '+(newCity?'btn-warning':'disabled btn-secondary')"
                                        @click.prevent="addCity()"
                                        :disabled="newCity == null">
                                    Add
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">City</th>
                                    <th scope="col" class="d-none d-sm-table-cell">County</th>
                                    <th scope="col">State</th>
                                    <th scope="col">Zipcodes</th>
                                    <th scope="col">Cost</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <transition name="fade">
                                    <tbody v-if="selectedCities.length == 0">
                                    <tr>
                                        <td colspan="7">No cities codes selected yet.</td>
                                    </tr>
                                    </tbody>
                                </transition>
                                <transition-group name="zipcodestable" tag="tbody">
                                    <tr v-for="(city, index)  in selectedCities" :key="city.city">
                                        <th scope="row">{{index+1}}</th>
                                        <td>{{city.city}}</td>
                                        <td class="d-none d-sm-table-cell">{{city.county}}</td>
                                        <td>{{city.state}}</td>
                                        <td class="text-left">{{city.zipcodes}}</td>
                                        <td><b>${{city.zipcodes*zipcodeCost}}</b></td>
                                        <td><i class="bi bi-x-circle-fill delete-button text-danger"
                                               title="remove city"
                                               @click="removeCity(index)"> </i></td>
                                    </tr>
                                </transition-group>
                                <tr>
                                    <th colspan="7">Cost: ${{currentCost}} every week</th>
                                </tr>
                            </table>
                        </div>
                    </form>
                    <div class=" mb-3"
                         v-if="currentSubscriptionZipcodesQty !== currentZipcodes && currentZipcodes > 0">
                        <p><b>You must <span v-if="currentSubscription">re-</span>subscribe for
                                these changes to take effect:</b></p>
                        <form action="/create-checkout-session" method="POST">
                            <input v-for="(city, index)  in selectedCities" type="hidden"
                                   :name="'cities['+index+']'" :value="city.city"/>
                            <input v-for="(city, index)  in selectedCities" type="hidden"
                                   :name="'counties['+index+']'" :value="city.county"/>
                            <input v-for="(city, index)  in selectedCities" type="hidden"
                                   :name="'states['+index+']'" :value="city.state"/>
                            <button class="btn btn-warning mt-2 btn-lg" type="submit"><i class="bi bi-credit-card"></i>&nbsp;
                                <span v-if="currentSubscription">Re-</span>Subscribe with debit/credit card
                            </button>
                        </form>
                    </div>
                    <div class=" mt-2" v-if="currentSubscription && currentSubscription.willRenew">
                        <form method="POST">
                            <button class="btn btn-danger my-2 btn-lg" name="cancel"
                                    onclick="return confirm('Are you sure you want to cancel your subscription? (your advertisement will still be active until the end of the current period)')">
                                Cancel subscription
                            </button>
                            <br/>
                            To change the payment method, cancel your current subscription and create a new one.
                        </form>
                    </div>
                    <form action="/create-checkout-session" method="POST"
                          v-if="currentSubscription && !currentSubscription.willRenew">
                        <input v-for="(city, index)  in selectedCities" type="hidden"
                               :name="'cities['+index+']'" :value="city.city"/>
                        <input v-for="(city, index)  in selectedCities" type="hidden"
                               :name="'counties['+index+']'" :value="city.county"/>
                        <input v-for="(city, index)  in selectedCities" type="hidden"
                               :name="'states['+index+']'" :value="city.state"/>
                        <button class="btn btn-warning my-2 btn-lg" type="submit"><i class="bi bi-credit-card"></i>&nbsp;
                            Create new subscription
                        </button>

                    </form>
                    <?php if (!$subscription && !$lastInactiveSubscription) { ?>
                        <!--  <div class="p-2 mb-2 text-success text-center">The first week is free!</div> -->
                    <?php } ?>

                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    const ZipcodesSelector = {
        setup(props) {
            const zipcodeCost = 2;
            //const zipcodes = {<?php //foreach ($zipcodes as $zipcode) echo "'" . $zipcode['zipcode'] . "':{zipcode:'" . $zipcode['zipcode'] . "',city:'" . $zipcode['city'] . "',state:'" . $zipcode['state'] . "'}," ?>};
            //const cities = {<?php //foreach ($zipcodes as $zipcode) echo "'" . $zipcode['zipcode'] . "':{zipcode:'" . $zipcode['zipcode'] . "',city:'" . $zipcode['city'] . "',state:'" . $zipcode['state'] . "'}," ?>};
            const citiesTaken = [<?php foreach ($citiesTaken as $cityTaken) echo '{state: "' . $cityTaken['state'] . '",county: "' . $cityTaken['county'] . '", city: "' . $cityTaken['city'] . '"},'?>];
            const states = [<?php foreach ($states as $state) echo '{code:"' . $state['state_code'] . '", name:"' . $state['state'] . '"},' ?>];
            // const showSubscribeButton = Vue.ref(false);
            const currentSubscriptionZipcodesQty = <?php echo $subscriptionZipcodesQty; ?>;
            const currentSubscription = <?php if ($subscription) echo "{id:" . $subscription->getId() . ",lastBilling:'" . $subscription->getLastBilling() . "',willRenew:" . $subscription->getWillRenew() . "}"; else echo 'null'; ?>;
            const selectedCities = Vue.reactive([<?php
                if (sizeof($subscriptionCities) > 0) {
                    foreach ($subscriptionCities as $city)
                        echo "{zipcodes:" . $city['zipcodes'] . ",city:'" . $city['city'] . "',state:'" . $city['state'] . "',county:'" . $city['county'] . "'},";
                } elseif (sizeof($lastInactiveSubscriptionCities) > 0) {
                    foreach ($lastInactiveSubscriptionCities as $city)
                        echo "{zipcodes:" . $city['zipcodes'] . ",city:'" . $city['city'] . "',state:'" . $city['state'] . "',county:'" . $city['county'] . "'},";
                }

                ?>]);
            //  const newZipcode = Vue.ref(null);
            const newState = Vue.ref(null);
            const newCounty = Vue.ref(null);
            const newCity = Vue.ref(null);
            const selectedStateCounties = Vue.reactive([]);
            const selectedCountyCities = Vue.reactive([]);

            /* const addZipcode = () => {

                 if (zipcodesTaken.find((str) => str === newZipcode.value)) {
                     //found!
                     alert("Zip code is already in use by another sponsor. Please consider choosing a different one.");
                     return;
                 }

                 //  console.log(zipcodes.hasOwnProperty(newZipcode.value));
                 if (zipcodes.hasOwnProperty(newZipcode.value)) {
                     //check that it's not added already
                     for (let i = 0; i < selectedZipcodes.length; i++)
                         if (selectedZipcodes[i].zipcode === newZipcode.value) {
                             alert("ZIP code already added!");
                             return;
                         }
                     selectedZipcodes.push(zipcodes[newZipcode.value]);
                     newZipcode.value = null;

                 } else {
                     alert("Non existing USA ZIP code!");
                 }
             }*/
            const addCity = () => {
                //verify that is not taken
                for (let i = 0; i < citiesTaken.length; i++) {
                    if (citiesTaken[i].city === newCity.value && citiesTaken[i].county === newCounty.value && citiesTaken[i].state === newState.value) {
                        alert("City is already in use by another sponsor. Please consider choosing a different one.");
                        return;
                    }
                }
                //verify is not repeated
                for (let i = 0; i < selectedCities.length; i++) {
                    console.log(selectedCities[i].city, newCity.value.city, selectedCities[i].state, newState.value, selectedCities[i].county, newCounty.value);
                    if (selectedCities[i].city === newCity.value.city && selectedCities[i].state === newState.value && selectedCities[i].county === newCounty.value) {
                        alert("City already selected");
                        return;
                    }
                }

                selectedCities.push({
                    city: newCity.value.city,
                    state: newState.value,
                    county: newCounty.value,
                    zipcodes: parseInt(newCity.value.zipcodes)
                });
            }


            /*   const removeZipcode = (index) => {
                   selectedZipcodes.splice(index, 1);
                   if (selectedZipcodes.length === 0 && currentSubscription)
                       alert("No zipcodes selected! Please choose a new one or cancel your current subscription.");

               }*/

            const removeCity = (index) => {
                selectedCities.splice(index, 1);
                if (selectedCities.length === 0 && currentSubscription)
                    alert("No cities selected! Please choose a new one or cancel your current subscription.");
            }

            /*  const stripeCheckout = () => {

              }*/

            const cancelSubscription = () => {

            }

            const formatDate = (date) => {
                return moment(String(date)).format('D MMM YYYY')
            }

            /*   const selectedZipcodesArray = Vue.computed(() => {
                   let arrayAsString = "[";
                   for (let i = 0; i < selectedZipcodes.length; i++)
                       arrayAsString += selectedZipcodes[i].zipcode + ",";
                   arrayAsString += "]";
                   return arrayAsString;
               })*/

            const currentCost = Vue.computed(() => {
                let cost = 0;
                for (let i = 0; i < selectedCities.length; i++) {
                    cost += selectedCities[i].zipcodes * zipcodeCost;
                }
                return cost;
            })

            const currentZipcodes = Vue.computed(() => {
                let zipcodescount = 0;
                for (let i = 0; i < selectedCities.length; i++)
                    zipcodescount += selectedCities[i].zipcodes;
                return zipcodescount;
            });

            Vue.watch(newState, (state, prevState) => {
                axios.get('/ajax/getUSAData.php?action=get_state_counties&state=' + state)
                    .then(function (response) {
                       // console.log(response);
                        // handle success
                        Object.assign(selectedStateCounties, response.data.counties);
                        Object.assign(selectedCountyCities, []);
                        newCity.value = null;
                        newCounty.value = null;
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                        alert("Problem loading state counties!");
                        Object.assign(selectedStateCounties, []);
                    });
            })

            Vue.watch(newCounty, (county, prevCounty) => {
                axios.get('/ajax/getUSAData.php?action=get_county_cities&county=' + county + '&state=' + newState.value)
                    .then(function (response) {
                      //  console.log(response);
                        // handle success
                        Object.assign(selectedCountyCities, response.data.cities);
                        newCity.value = null;

                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                        alert("Problem loading state counties!");
                        Object.assign(selectedCountyCities, []);
                    });
            })

            return {
                /*   showSubscribeButton,*/
                /*  zipcodes,*/ states,
                currentSubscriptionZipcodesQty,
                selectedCities,
                /*  addZipcode,*/addCity,
                /*  newZipcode,*/ newCity,
                newCounty, newState,
                removeCity, currentSubscription,
                /*  stripeCheckout,*/ formatDate,
                cancelSubscription, currentCost,
                selectedStateCounties, selectedCountyCities,
                currentZipcodes, zipcodeCost,
            }
        }
    }
    Vue.createApp(ZipcodesSelector).mount('#zipcodes_selector')
</script>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/footer.php'); ?>
