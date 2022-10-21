<?php

require_once('User.php');

class Account
{

    private $con;
    private $errorArray = array();

    public function __construct($con)
    {
        $this->con = $con;
    }

    public function login($un, $pw)
    {
        $pw = hash("sha512", $pw);

        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un AND password=:pw");
        $query->bindParam(":un", $un);
        $query->bindParam(":pw", $pw);

        $query->execute();

        if ($query->rowCount() == 1) {
            $data = $query->fetch(PDO::FETCH_ASSOC);
            $user = new User($this->con, null, $data['id']);
          /*  if ($data['email_validation_code'] != null) {
                $this->sendConfirmationEmail($user);
                array_push($this->errorArray, Constants::$emailAddressNotConfirmed);
                return false;
            }*/
            return true;
        } else {
            array_push($this->errorArray, Constants::$loginFailed);
            return false;
        }
    }

    public function register($un, $em, $em2, $pw, $pw2, $zipCode, $sponsorUser = false)
    {
        $this->validateUsername($un);
        $this->validateEmails($em, $em2);
        $this->validatePasswords($pw, $pw2);
        if ($zipCode)
            $this->validateZipcode($zipCode);

        if (empty($this->errorArray)) {
            $wasSuccesful = $this->insertUserDetails($un, $em, $pw, $zipCode, $sponsorUser ? 'sponsor' : 'user');
            $user = $this->findUserByEmail($em);
          /*  if (!$sponsorUser)
                $this->sendConfirmationEmail($user);*/
            return $user;
        } else {
            return false;
        }
    }

    public function registerWithGoogle($un, $em, $em2, $pw, $pw2, $zipCode)
    {
        $this->validateUsername($un);
        $this->validateEmails($em, $em2);
        $this->validatePasswords($pw, $pw2);
        $this->validateZipcode($zipCode);

        if (empty($this->errorArray)) {
            $wasSuccesful = $this->insertUserDetails($un, $em, $pw, $zipCode);
            $user = $this->findUserByEmail($em);
            return $user;
        } else {
            return false;
        }
    }

    public function updateDetails($em, $un)
    {
        $this->validateNewEmail($em, $un);


        if (empty($this->errorArray)) {
            $query = $this->con->prepare("UPDATE users SET email=:em WHERE username=:un");
            $query->bindParam(":em", $em);
            $query->bindParam(":un", $un);

            return $query->execute();
        } else {
            return false;
        }
    }

    public function updatePassword($oldPw, $newPw, $newPw2, $un)
    {
        if ($oldPw) //won't validate password if they forgot it
            $this->validateOldPassword($oldPw, $un);
        $this->validatePasswords($newPw, $newPw2);

        if (empty($this->errorArray)) {
            $query = $this->con->prepare("UPDATE users SET password=:pw WHERE username=:un");
            $pw = hash("sha512", $newPw);
            $query->bindParam(":pw", $pw);
            $query->bindParam(":un", $un);

            return $query->execute();
        } else {
            return false;
        }
    }

    private function validateOldPassword($oldPw, $un)
    {
        $pw = hash("sha512", $oldPw);

        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un AND password=:pw");
        $query->bindParam(":un", $un);
        $query->bindParam(":pw", $pw);

        $query->execute();

        if ($query->rowCount() == 0) {
            array_push($this->errorArray, Constants::$passwordIncorrect);
        }
    }

    public function insertUserDetails($un, $em, $pw, $zipCode, $role = 'user')
    {

        $pw = hash("sha512", $pw);
        $profilePic = "/assets/images/profilePictures/default.png";

        $query = $this->con->prepare("INSERT INTO users (username, email, password, profilePic, zipcode, role)
                                        VALUES(:un, :em, :pw, :pic, :zc, :role)");

        $query->bindParam(":un", $un);
        $query->bindParam(":em", $em);
        $query->bindParam(":pw", $pw);
        $query->bindParam(":pic", $profilePic);
        $query->bindParam(":zc", $zipCode);
        $query->bindParam(":role", $role);

        $wasSuccessful = $query->execute();

        if (!$wasSuccessful) {
            echo $query->errorInfo();
        }

        return $wasSuccessful;
    }


    private function validateUsername($un)
    {
        if (strlen($un) > 25 || strlen($un) < 5) {
            array_push($this->errorArray, Constants::$usernameCharacters);
            return;
        }
        if (!preg_match('/^[\w\d\ ]*$/', $un)) {
            array_push($this->errorArray, Constants::$usernameStrangeCharacters);
            return;
        }

        $query = $this->con->prepare("SELECT username FROM users WHERE username=:un");
        $query->bindParam(":un", $un);
        $query->execute();

        if ($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$usernameTaken);
        }

    }

    private function validateEmails($em, $em2, $currentUserId = null)
    {
        if ($em != $em2) {
            array_push($this->errorArray, Constants::$emailsDoNotMatch);
            return;
        }

        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
        }

        $query = $this->con->prepare("SELECT email FROM users WHERE email=:em " . ($currentUserId ? " AND id != :userId" : ""));
        $query->bindParam(":em", $em);
        if ($currentUserId)
            $query->bindParam(":userId", $currentUserId);
        $query->execute();

        if ($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$emailTaken);
        }

    }

    private function validateNewEmail($em, $username)
    {

        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
        }

        $query = $this->con->prepare("SELECT email FROM users WHERE email=:em AND username != :un");
        $query->bindParam(":em", $em);
        $query->bindParam(":un", $username);
        $query->execute();

        if ($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$emailTaken);
        }

    }

    public function validatePasswords($pw, $pw2)
    {
        if ($pw != $pw2) {
            array_push($this->errorArray, Constants::$passwordsDoNotMatch);
            return;
        }

        if (preg_match("/[^A-Za-z0-9]/", $pw)) {
            array_push($this->errorArray, Constants::$passwordNotAlphanumeric);
            return;
        }

        if (strlen($pw) > 30 || strlen($pw) < 5) {
            array_push($this->errorArray, Constants::$passwordLength);
        }
    }

    public function validateZipcode($zipCode)
    {
        //get zip codes in array
        $query = $this->con->prepare("SELECT DISTINCT(CONVERT(zipcode,SIGNED)) FROM zipcodes ");
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_GROUP);
        if (!isset($result[intval($zipCode)]))
            array_push($this->errorArray, Constants::$invalidZipcode);
    }


    public function getError($error)
    {
        if (in_array($error, $this->errorArray)) {
            return "<span class='errorMessage'>$error</span>";
        }
    }

    public function getFirstError()
    {
        if (!empty($this->errorArray)) {
            return $this->errorArray[0];
        } else {
            return "";
        }
    }

    public function update($user, $em, $zipCode, $allowEmailMessages)
    {
        //  $this->validateUsername($un);
        $this->validateEmails($em, $em, $user->getId());
        $this->validateZipcode($zipCode);

        if (empty($this->errorArray)) {
            return $this->updateUserDetails($user, $em, $zipCode, $allowEmailMessages);
        } else {
            return false;
        }
    }

    public function updateUserDetails($user, $em, $zipCode, $allowEmailMessages)
    {
        $newPicturePath = $this->uploadProfilePicture($user);

        if (sizeof($this->errorArray) > 0)
            return false;//don't continue if there was an error with the file upload

        $query = $this->con->prepare("UPDATE users  SET email = :em, zipcode = :zc " . ($newPicturePath ? ",profilePic = :pic" : "") . ", allow_email_messages = :awm WHERE id = :userId");

        $query->bindParam(":em", $em);
        if ($newPicturePath)
            $query->bindParam(":pic", $newPicturePath);
        $query->bindParam(":zc", $zipCode);
        $userId = $user->getId();
        $query->bindParam(":userId", $userId);
        $query->bindParam(":awm", $allowEmailMessages);

        $wasSuccessful = $query->execute();

        if (!$wasSuccessful) {
            echo $query->errorInfo();
        }

        return $wasSuccessful;
    }

    /**
     * Validates file upload if there is one
     * @param $user User object
     * @return string | null new file path
     */
    public function uploadProfilePicture($user)
    {
        if ($_FILES['profile_picture']['size'] == 0 && ($_FILES['profile_picture']['error'] == 0 || $_FILES['profile_picture']['error'] == 4)) {
            return null;//no upload
        }
        if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            array_push($this->errorArray, Constants::$uploadFailed);
            return null;
        }

        $info = getimagesize($_FILES['profile_picture']['tmp_name']);
        if ($info === FALSE) {
            array_push($this->errorArray, Constants::$unrecognizedImageType);
            return null;
        }

        if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG) && ($info[2] !== IMAGETYPE_JPEG2000) && ($info[2] !== IMAGETYPE_WEBP)) {
            array_push($this->errorArray, Constants::$invalidImageType);
            return null;
        }

        if ($_FILES['profile_picture']['size'] > 10485760) { //10 MB (size is also in bytes)
            // File too big
            array_push($this->errorArray, Constants::$imageTooBig);
            return null;
        }
        //remove previous file... unless it's the hardcoded one ಠ_ಠ
        if ($user->getProfilePic() && $user->getProfilePic() != "/assets/images/profilePictures/default.png")
            @unlink($_SERVER['DOCUMENT_ROOT'] . "/" . $user->getProfilePic());

        $basePath = "/assets/images/profilePictures/";
        $path_parts = pathinfo($_FILES["profile_picture"]["name"]);
        $extension = $path_parts['extension'];
        $fileName = $user->getId() . '_' . $user->getUsername() . "_pp." . $extension;

        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . "/" . $basePath . $fileName);

        return $basePath . $fileName;
    }

    /**
     * Generates a random key for a user to recover their password
     * @param $user User object
     * @return string
     */
    public function generatePasswordRecoveryKey(User $user): string
    {
        $randomString = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 10))), 1, 10);
        $query = $this->con->prepare("UPDATE users SET password_recovery_code=:pk WHERE id=:id");
        $query->bindParam(":pk", $randomString);
        $query->bindParam(":id", $user->getId());

        if ($query->execute())
            return $randomString;
    }

    /**
     * Finds a user by their emial
     * @param $email string
     * @return User object | null
     */
    public function findUserByEmail($email): ?User
    {
        $query = $this->con->prepare("SELECT id  FROM users WHERE email = :email");
        $query->bindParam(":email", $email);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);

        if ($data)
            return new User($this->con, null, $data['id']);
        else {
            array_push($this->errorArray, Constants::$emailNotFound);
            return null;
        }

    }

    /**
     * Verifies that password recovery key exists, returns user
     * @param $key string
     * @return User
     */
    public function verifyPasswordRecoveryKey($key)
    {
        $query = $this->con->prepare("SELECT id FROM users WHERE password_recovery_code = :key");
        $query->bindParam(":key", $key);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);

        if ($data)
            return new User($this->con, null, $data['id']);

    }

    /**
     * sets validation key and sends confirmation email to user
     * @param $user User object
     */
   /* public function sendConfirmationEmail($user)
    {
        //set email validation code
        $randomString = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 10))), 1, 10);
        $query = $this->con->prepare("UPDATE users SET email_validation_code=:emv WHERE id=:id");
        $userId = $user->getId();
        $query->bindParam(":emv", $randomString);
        $query->bindParam(":id", $userId);
        $query->execute();

        $confirmEmailUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/confirm_email.php?key=" . $randomString . '&id=' . $userId;

        $body = "To confirm that this is your email account, please go to the next url: <a href='$confirmEmailUrl'>$confirmEmailUrl</a>";
        $headers = "From: ELegendz <noreply@elegendz.net>" . "\r\n" . "Reply-To: noreply@elegendz.net" . "\r\n" . "X-Mailer: PHP/" . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $wasSuccessful = mail($user->getEmail(), '[ELEGENDZ] Email confirmation', $body, $headers);
    }*/

    /**
     * Verifies that email confirmation key exists, updates user account to valid and returns user (null if not found)
     * @param $user User object
     * @param $key string
     * @return bool
     */
    public function verifyEmailConfirmationKey($user, $key)
    {
        $query = $this->con->prepare("SELECT id FROM users WHERE email_validation_code = :key AND id = :id");
        $userId = $user->getId();
        $query->bindParam(":key", $key);
        $query->bindParam(":id", $userId);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);

        if ($data) {

            $query = $this->con->prepare("UPDATE users SET email_validation_code=NULL WHERE id=:id");//means it's been validated
            $query->bindParam(":id", $user->getId());
            $query->execute();
            return true;
        } else
            return false;

    }

}

?>