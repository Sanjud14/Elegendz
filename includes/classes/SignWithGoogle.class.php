<?php
require_once("Account.php");
require_once("Constants.php");
require_once("../../includes/log.php");
class Connect extends PDO
{
    public function __construct()
    {
        $dsn = "mysql:host=localhost;dbname=elegendz";
        $username = "root";
        $passwd = "JZqKmyBUm0ZLSrFR";
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
}

class Controller
{
    // check if user is logged in
    function checkUserStatus($id, $sess)
    {
        $db = new Connect;
        $user = $db->prepare("SELECT id FROM users WHERE id=:id AND session=:session");
        $user->execute([
            ':id' => intval($id),
            ':session' => $sess
        ]);
        $userInfo = $user->fetch(PDO::FETCH_ASSOC);
        if (!$userInfo["id"]) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // inset data
    function insertData($data)
    {
        $db = new Connect;
        $account = new Account($db);
        $checkUser = $db->prepare('SELECT * FROM users WHERE email=:email');
        $checkUser->execute(['email' => $data['email']]);
        $info = $checkUser->fetch(PDO::FETCH_ASSOC);
        if (!$info['id']) {
            $email = $data['email'];
            $username = $data['familyName'] . $data['givenName'];
            header("Location:../../signUpWithGoogle.php?email=$email&username=$username");

        } else {
            $user = new User($db, $data['familyName'] . $data['givenName']);
            $_SESSION["userLoggedIn"] = $data['familyName'] . $data['givenName'];
            $_SESSION["userRole"] = $user->getRole();
            if ($user->isAdmin()) {
                header("Location: /admin/index");
            } else {
                if (isset($_SESSION["originatingpage"]))
                    header("Location: https://" . $_SESSION["originatingpage"]);
                else
                    header("Location: /index");
            }
        }
    }

    // function for generating password and login session
    function generateCode($length)
    {
        $chars = "vwxyzABCD02789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }
        return $code;
    }

}

?>
