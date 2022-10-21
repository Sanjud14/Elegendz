<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/elegendz/vendor/autoload.php');

class SponsorSubscription
{
    private $con, $sqlData, $userLoggedInObj;

    public function __construct($con, $input, $userLoggedInObj, $stripeId = null)
    {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;

        if (is_array($input)) {
            $this->sqlData = $input;
        } else {
            if ($input) {
                $query = $this->con->prepare("SELECT * FROM sponsors_subscriptions WHERE id = :id");
                $query->bindParam(":id", $input);
            } elseif ($stripeId) {
                $query = $this->con->prepare("SELECT * FROM sponsors_subscriptions WHERE stripe_subscription_id = :stripeId");
                $query->bindParam(":stripeId", $stripeId);
            }
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function getId()
    {
        return $this->sqlData["id"];
    }

    public function getLastBilling()
    {
        return $this->sqlData["last_billing"];
    }

    public function getStripeSubscriptionId()
    {
        return $this->sqlData["stripe_subscription_id"];
    }

    public function getWillRenew()
    {
        return $this->sqlData["will_renew"];
    }

    /**
     * Returns subscription's zipcodes as array of arrays
     * @return mixed
     */
    /* public function getZipcodes()
     {
         $query = $this->con->prepare("SELECT sponsors_subscriptions_zipcodes.*,zipcodes.city,zipcodes.state  FROM sponsors_subscriptions_zipcodes
     INNER JOIN zipcodes ON sponsors_subscriptions_zipcodes.zipcode = zipcodes.zipcode WHERE subscription_id = :id GROUP BY zipcodes.zipcode");
         $id = $this->getId();
         $query->bindParam(":id", $id);
         $query->execute();
         return $query->fetchAll(PDO::FETCH_ASSOC);
     }*/

    /**
     * Returns subscription's cities as array of arrays
     * @return mixed
     */
    public function getCities()
    {
        $query = $this->con->prepare("SELECT zipcodes.city,zipcodes.county,zipcodes.state, COUNT(zipcodes.zipcode) AS zipcodes  FROM sponsors_subscriptions_cities
    INNER JOIN zipcodes ON sponsors_subscriptions_cities.city = zipcodes.city AND sponsors_subscriptions_cities.county = zipcodes.county AND sponsors_subscriptions_cities.state = zipcodes.state
    WHERE subscription_id = :id GROUP BY zipcodes.zipcode");
        $id = $this->getId();
        $query->bindParam(":id", $id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marks current subscription as active, updates fields
     * @param $amount integer amount paid
     * @param $subscriptionId string stripe subscription id
     */
    public function activateSubscription($amount, $subscriptionId)
    {
        $query = $this->con->prepare("UPDATE sponsors_subscriptions SET good_until = (NOW() + INTERVAL 15 day) ,amount_paid = :amount, last_billing = NOW(), stripe_subscription_id = :stripeSubscriptionId WHERE id = :id");
        $query->bindParam(":amount", $amount);
        $query->bindParam(":stripeSubscriptionId", $subscriptionId);
        $id = $this->getId();
        $query->bindParam(":id", $id);
        $query->execute();
    }

    /**
     * Cancels subscription on Stripe, updates the database
     */
    public function cancelStripeSubscription()
    {
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        \Stripe\Subscription::update(
            $this->getStripeSubscriptionId(),
            [
                'cancel_at_period_end' => true,
            ]
        );

        $query = $this->con->prepare("UPDATE sponsors_subscriptions SET will_renew = 0 WHERE id = :id");
        $id = $this->getId();
        $query->bindParam(":id", $id);

        $query->execute();
    }

    /**
     * Extends subscription for another week
     * @param $amount integer
     */
    public function renewSubscription($amount)
    {
        $query = $this->con->prepare("UPDATE sponsors_subscriptions SET good_until = (NOW() + INTERVAL 15 day) ,amount_paid = :amount, last_billing = NOW() WHERE id = :id");
        $query->bindParam(":amount", $amount);
        $id = $this->getId();
        $query->bindParam(":id", $id);
        $query->execute();
    }
}