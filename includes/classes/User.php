<?php

class User
{

    private $con, $sqlData;

    const ROLE_USER = "user";
    const ROLE_ADMIN = "admin";
    const ROLE_SPONSOR = "sponsor";

    public function __construct($con, $username, $id = null, $stripeCustomer = null)
    {
        $this->con = $con;

        if ($username) {
            $query = $this->con->prepare("SELECT * FROM users WHERE username = :un");
            $query->bindParam(":un", $username);
        } elseif ($stripeCustomer) {
            $query = $this->con->prepare("SELECT * FROM users WHERE stripe_customer = :sc");
            $query->bindParam(":sc", $stripeCustomer);
        } else {//id
            $query = $this->con->prepare("SELECT * FROM users WHERE id = :ui");
            $query->bindParam(":ui", $id);
        }
        $query->execute();

        $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION["userLoggedIn"]);
    }

    public function getId()
    {
        return $this->sqlData["id"];
    }

    public function getUsername()
    {
        return $this->sqlData["username"];
    }

    public function getEmail()
    {
        return $this->sqlData["email"];
    }

    public function getProfilePic()
    {
        return $this->sqlData["profilePic"];
    }

    public function getSignUpDate()
    {
        return $this->sqlData["signUpDate"];
    }

    public function getZipcode()
    {
        return $this->sqlData["zipcode"];
    }

    public function getAllowEmailMessages()
    {
        return $this->sqlData["allow_email_messages"];
    }

    public function getEmailValidationCode()
    {
        return $this->sqlData["email_validation_code"];
    }

    public function getRole()
    {
        return $this->sqlData["role"];
    }

    public function getSponsorId()
    {
        return $this->sqlData["sponsor_id"];
    }

    public function getStripeCustomer()
    {
        return $this->sqlData["stripe_customer"];
    }

    public function isSubscribedTo($userTo): bool
    {
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo=:userTo AND userFrom=:userFrom");
        $username = $this->getUsername();
        $query->bindParam(":userTo", $userTo);
        $query->bindParam(":userFrom", $username);
        $query->execute();
        return $query->rowCount() > 0;
    }

    public function getSubscriberCount()
    {
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo=:userTo");
        $query->bindParam(":userTo", $username);
        $username = $this->getUsername();
        $query->execute();
        return $query->rowCount();
    }

    public function getSubscriptions()
    {
        $query = $this->con->prepare("SELECT userTo FROM subscribers WHERE userFrom=:userFrom");
        $username = $this->getUsername();
        $query->bindParam(":userFrom", $username);
        $query->execute();

        $subs = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row["userTo"]);
            array_push($subs, $user);

        }
        return $subs;
    }

    public function getLastUploadedVideo()
    {
        $query = $this->con->prepare("SELECT id FROM videos WHERE uploadedBy=:user ORDER BY id DESC LIMIT 1");
        $username = $this->getUsername();
        $query->bindParam(":user", $username);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * Return categories chosen by the user as array
     * @return array
     */
    public function getCategories()
    {
        $query = $this->con->prepare("SELECT category_id,name FROM users_categories LEFT JOIN categories ON users_categories.category_id = categories.id WHERE user_id=:userId");
        $userId = $this->getId();
        $query->bindParam(":userId", $userId);
        $query->execute();

        /*  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
              $user = new User($this->con, $row["userTo"]);
              array_push($subs, $user);

          }*/
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns all users as array
     * @param $con PDO connection object
     * @return array
     */
    public static function getAllUsers($con)
    {
        $query = $con->prepare("SELECT users.id,username,state FROM users LEFT JOIN zipcodes ON users.zipcode= zipcodes.zipcode GROUP BY users.id");

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates user categories
     * @param $categories array of integer
     */
    public function saveUserCategories($categories)
    {
        //first delete any existing
        $query = $this->con->prepare("DELETE FROM users_categories WHERE user_id=:userId ");
        $userId = $this->getId();
        $query->bindParam(":userId", $userId);
        $query->execute();

        foreach ($categories as $category) {
            $query = $this->con->prepare("INSERT INTO users_categories(user_id, category_id) VALUES(:uid, :cid)");
            $userId = $this->getId();
            $query->bindParam(":uid", $userId);
            $query->bindParam(":cid", $category);
            $query->execute();
        }
    }

    /**
     * Returns users subscribed to his user as array of arrays
     * @return array
     */
    public function getSubscribers(): array
    {
        $query = $this->con->prepare("SELECT users.* FROM subscribers INNER JOIN users ON subscribers.userFrom = users.username WHERE userTo=:userTo");
        $query->bindParam(":userTo", $username);
        $username = $this->getUsername();
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns number of new notifications (unread) for the user
     * @return integer
     */
    public function getNewNotificationsAmount()
    {
        $query = $this->con->prepare("SELECT count(*) as 'count' FROM notifications WHERE user_id = :userId AND already_read = 0");
        $userId = $this->getId();
        $query->bindParam(":userId", $userId);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);
        return $data["count"];
    }

    /**
     * Deletes password recovery key
     */
    public function resetPasswordRecoveryKey()
    {
        $query = $this->con->prepare("UPDATE users SET password_recovery_code=NULL WHERE id=:id");
        $userId = $this->getId();
        $query->bindParam(":id", $userId);

        return $query->execute();
    }

    public function getProfilePictureFullPath()
    {
        //have to convert url to absolute instead of relative if it's not!
        $profilePicUrl = $this->getProfilePic();
        if (substr($profilePicUrl, 0, 1) == '/')
            return $profilePicUrl;
        else
            return '/' . $profilePicUrl;
    }

    public function isAdmin()
    {
        return $this->sqlData["role"] == User::ROLE_ADMIN;
    }

    public function isSponsor()
    {
        return $this->sqlData["role"] == User::ROLE_SPONSOR;
    }

    /**
     * Returns the sum of votes from the user's songs
     * @return integer
     */
    public function getTotalViews()
    {
        $query = $this->con->prepare("SELECT IFNULL(SUM(views),0) as 'total' FROM users LEFT JOIN videos ON videos.uploadedBy = users.username WHERE users.id = :userId");
        $userId = $this->getId();
        $query->bindParam(":userId", $userId);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        return $data["total"];
    }

    /**
     * Checks if user has reached a rounded number of subscribers, and sends email notificaiton
     */
    public function checkSubscribersCongratulation()
    {
        $query = $this->con->prepare("SELECT count(*) as 'count' FROM subscribers WHERE userTo = :username");
        $username = $this->getUsername();
        $query->bindParam(":username", $username);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $subscribers = $data["count"];
        if ($subscribers > 0 && $subscribers % 100 == 0) {
            Notification::sendEmailNotification($subscribers . " subscribers!", "Congratulations!<br/>You have now reached $subscribers subscribers.", $this->getEmail());
        }
    }

}

