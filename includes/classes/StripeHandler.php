<?php
require_once('vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/includes/classes/User.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/includes/classes/Notification.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/includes/classes/Sponsor.php');
require_once("includes/log.php");

//require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/Notification.php');

class StripeHandler
{
    private $con, $profileUserObj;

    public function __construct($con, $profileUsername)
    {
        $this->con = $con;
        if ($profileUsername)
            $this->profileUserObj = new User($con, $profileUsername);
    }

    /**
     * Creates a Stripe customer, updates user with id
     * @param $user User object
     * @return string (customer id)
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCustomer($user)
    {
        $stripe = new \Stripe\StripeClient(
            STRIPE_SECRET_KEY
        );

        $sponsor = new Sponsor($this->con, $user->getSponsorId(), $user);

        $customer = $stripe->customers->create([
            'description' => $user->getUsername(),
            'email' => $user->getEmail(),
            'name' => $sponsor->getName(),
            'phone' => $sponsor->getPhone(),
        ]);
        $query = $this->con->prepare("UPDATE users SET stripe_customer=:stripeCustomer WHERE id=:id");
        $query->bindParam(":stripeCustomer", $customer->id);
        $userId = $user->getId();
        $query->bindParam(":id", $userId);

        $query->execute();
        return $customer->id;
    }

    /**
     * Handle request to create a new subscription, then redirects
     */
    public function preSubscribe()
    {
        // $username = $_SESSION["userLoggedIn"];
        if ($_SESSION["userRole"] != User::ROLE_SPONSOR) {
            //  header('HTTP/1.0 403 Forbidden');
            header("Location: /sign-in");
        }
        $cities = $_POST['cities'];//array
        $counties = $_POST['counties'];//array
        $states = $_POST['states'];//array

        if (User::isLoggedIn()) {
            $username = $_SESSION["userLoggedIn"];
            $userLoggedInObj = new User($this->con, $username);
        } else {
            header("Location: /sign-in");
        }
        $sponsorId = intval($userLoggedInObj->getSponsorId());
        $sponsor = new Sponsor($this->con, $sponsorId, $userLoggedInObj);
        // $previousSubscriptions = $sponsor->getAllSubscriptions();

//create subscription
        $query = $this->con->prepare("INSERT INTO sponsors_subscriptions(sponsor_id, good_until, created_at, amount_paid, last_billing, will_renew)
                             VALUES(:sponsorId, NULL, NOW(),NULL , NOW(),1);");

        $query->bindParam(":sponsorId", $sponsorId);

        $this->con->beginTransaction();
        $query->execute();

        $subscriptionId = $this->con->lastInsertId();
        $this->con->commit();
        //create subscription zipcode references
        $zipcodes = 0;
        foreach ($cities as $i => $city) {
            //check if a city is not currently in use
            $query = $this->con->prepare("SELECT city FROM sponsors_subscriptions_cities AS ssc INNER JOIN sponsors_subscriptions AS ss
            ON ssc.subscription_id = ss.id  WHERE good_until IS NOT NULL AND good_until >= NOW() AND ssc.city = :city AND sponsor_id != :sponsorId
            AND ssc.county = :county AND ssc.state = :state ");
            $query->bindParam(":city", $city);
            $query->bindParam(":county", $counties[$i]);
            $query->bindParam(":state", $states[$i]);
            $query->bindParam(":sponsorId", $sponsorId);
            $query->execute();
            $cityTaken = $query->fetch(PDO::FETCH_ASSOC);
            if ($cityTaken) {
                $_SESSION['message_display'] = 'Zip code ' . $city . ' is already in use by another sponsor. Please consider choosing a different one.';
                $_SESSION['message_display_type'] = 'danger';
                header("Location: /sponsor/status");
                exit;
            }


            $query = $this->con->prepare("INSERT INTO sponsors_subscriptions_cities(city, county, state, subscription_id )
                             VALUES(:city,:county,:state,:subscriptionId)");

            $query->bindParam(":subscriptionId", $subscriptionId);
            $query->bindParam(":city", $city);
            $query->bindParam(":county", $counties[$i]);
            $query->bindParam(":state", $states[$i]);

            $this->con->beginTransaction();
            $query->execute();
            $this->con->commit();

            //compute zipcodes amount
            $query = $this->con->prepare("SELECT COUNT(zipcode) AS zipcodes FROM zipcodes WHERE city = :city AND county = :county AND state = :state GROUP BY city");
            $query->bindParam(":city", $city);
            $query->bindParam(":county", $counties[$i]);
            $query->bindParam(":state", $states[$i]);
            $query->execute();
            $zipcodesResult = $query->fetch(PDO::FETCH_ASSOC);
            $zipcodes += $zipcodesResult['zipcodes'];
        }

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $priceId = STRIPE_PRICE_ID;

        // get customer id
        $customerId = $userLoggedInObj->getStripeCustomer();
        if (!$customerId)
            $customerId = $this->createCustomer($userLoggedInObj);

        $session = \Stripe\Checkout\Session::create([
            'success_url' => "https://$_SERVER[HTTP_HOST]" . '/sponsor/status?message_display=Payment was successful. You may not see the changes right away.&message_display_type=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => "https://$_SERVER[HTTP_HOST]/sponsor/status?message_display=Payment was cancelled&message_display_type=danger",
            'mode' => 'subscription',
            'customer' => $customerId,
            'client_reference_id' => $subscriptionId,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => $zipcodes,
            ]],
            'subscription_data' => [
                //'trial_period_days' => (sizeof($previousSubscriptions) == 0 ? STRIPE_FREE_TRIAL_DAYS : null),
            ],
        ]);


// Redirect to the URL returned on the Checkout Session.
// With vanilla PHP, you can redirect with:
        header("HTTP/1.1 303 See Other");
        header("Location: " . $session->url);

    }

    /**
     * Handle Stripe's notifications
     */
    public function webhook()
    {
        wh_log('Webhook call');
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);


        $endpoint_secret = STRIPE_ENDPOINT_SECRET;

        $payload = @file_get_contents('php://input');
        // wh_log('Webhook call payload:' . $payload);
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            echo '⚠️  Webhook error while parsing basic request.';
            http_response_code(400);
            exit();
        }
        if ($endpoint_secret) {
            // Only verify the event if there is an endpoint secret defined
            // Otherwise use the basic decoded event
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                // Invalid signature
                echo '⚠️  Webhook error while validating signature.';
                http_response_code(400);
                exit();
            }
        }

// Handle the event
        // Notification::sendEmailNotification('stripe notification: ' . $event->type, print_r($event, true), 'freelance.frivas@gmail.com');
        switch ($event->type) {
            case 'payment_intent.succeeded':

                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // Then define and call a method to handle the successful attachment of a PaymentMethod.
                // handlePaymentMethodAttached($paymentMethod);
                break;
            case 'checkout.session.completed':
                //Sent when a customer clicks the Pay or Subscribe button in Checkout, informing you of a new purchase.
                $stripe = new \Stripe\StripeClient(
                    STRIPE_SECRET_KEY
                );
                $session = $event->data->object; // contains a \Stripe\PaymentIntent
                wh_log('Transaction finished for:' . $session->customer);
                $user = new User($this->con, null, null, $session->customer);
                $sponsor = new Sponsor($this->con, $user->getSponsorId(), null);
                //retrieve subscription
                $subscription = new SponsorSubscription($this->con, $session->client_reference_id, null);
                // activateSubscription
                $subscription->activateSubscription($session->amount_total / 100, $session->subscription);
                //cancel any other active subscription
                $query = $this->con->prepare("SELECT * FROM sponsors_subscriptions WHERE id != :id AND will_renew= 1 
                AND sponsor_id = :sponsorId AND stripe_subscription_id IS NOT NULL");
                $subId = $subscription->getId();
                $query->bindParam(":id", $subId);
                $sponsorId = $sponsor->getId();
                $query->bindParam(":sponsorId", $sponsorId);
                $query->execute();
                $previousSubscriptionArray = $query->fetch(PDO::FETCH_ASSOC);
                if ($previousSubscriptionArray) {
                    $previousSubscription = new SponsorSubscription($this->con, $previousSubscriptionArray, null);

                    $stripe->subscriptions->cancel(
                        $previousSubscription->getStripeSubscriptionId(),
                        []
                    );
                    $query = $this->con->prepare("UPDATE sponsors_subscriptions SET will_renew = 0, good_until = NULL WHERE id = :id");
                    $prevSubId = $previousSubscription->getId();
                    $query->bindParam(":id", $prevSubId);
                    $query->execute();
                }

                //notify
                $cancelUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/sponsor/status";
                $body = "Thank you for subscribing to the ELegendz advertisement system. You can cancel it or modify it at anytime from <a href='" . $cancelUrl . "'>" . $cancelUrl . "</a>.<br/>";
                $zipcodes = $subscription->getZipcodes();
                //   if ($sponsor->getLogo()) {
                $body .= "Your advertisement is currently being displayed for the zipcodes ";
                foreach ($zipcodes as $i => $zipcode)
                    $body .= ($i > 0 ? ", " : "") . $zipcode['zipcode'];

                $body .= ".";
                /*   } else {
                       $updateUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/sponsor/business-data";
                       $body .= "You still haven't uploaded an advertisement image. You can upload it from. <a href='$updateUrl'>" . $updateUrl . "</a>";
                   }*/
                Notification::sendEmailNotification("Your subscription is active", $body, $user->getEmail());
                break;
            case 'invoice.paid':
                //	Sent each billing interval when a payment succeeds.
                $invoice = $event->data->object; // contains a \Stripe\PaymentIntent
                wh_log('Subscription renewed for:' . $invoice->customer_name);
                $subscription = new SponsorSubscription($this->con, null, null, $invoice->subscription);
                $subscription->renewSubscription($invoice->amount_paid / 100);
                break;
            case 'invoice.payment_failed':
                //	Sent each billing interval if there is an issue with your customer’s payment method.
                $payment = $event->data->object; // contains a \Stripe\PaymentIntent

                $user = new User($this->con, null, null, $payment->customer);
                //retrieve subscription
                $subscription = new SponsorSubscription($this->con, $payment->client_reference_id, null);
                wh_log('Subscription payment failed for:' . $user->getUsername());
                //only notify user if this is NOT the first payment
                if ($subscription->getLastBilling() != null) {
                    $updateUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/sponsor/status";
                    $body = "There was a problem collecting your last weekly payment. You can create a new subscription with a different payment method from <a href='$updateUrl'>$updateUrl</a>.";
                    Notification::sendEmailNotification("Failed subscription payment", $body, $user->getEmail());
                    break;
                }
            default:
                // Unexpected event type
                //  error_log('Received unknown event type');
        }

        http_response_code(200);
    }

}