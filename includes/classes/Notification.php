<?php

require_once("ButtonProvider.php");
require_once("CommentControls.php");

class Notification
{
    private $con, $sqlData, $userLoggedInObj;

    public function __construct($con, $input, $userLoggedInObj)
    {
        if (!is_array($input)) {
            $query = $con->prepare("SELECT * FROM notifications where id=:id");
            $query->bindParam(":id", $input);
            $query->execute();

            $input = $query->fetch(PDO::FETCH_ASSOC);
        }

        $this->sqlData = $input;
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    /**
     * Retrieves all user notifications with author data
     * @param $con PDO object
     * @param $userId integer
     * @param $markAllRead boolean whether all notifications should be mark as read
     * @param $limit integer | null max number of notifications retrieved, null for no limit
     * @return Array of arrays
     */
    public static function retrieveUserNotifications($con, $userId, $markAllRead, $limit = null)
    {
        $query = $con->prepare("SELECT notifications.*,users.username as author_username, users.profilePic as author_picture FROM notifications LEFT JOIN users ON notifications.author_id = users.id
        WHERE user_id = :userId ORDER BY id DESC" . ($limit ? " LIMIT $limit" : ""));
        $query->bindParam(":userId", $userId);
        $query->execute();
        $notifications = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($markAllRead) {
            $query = $con->prepare("UPDATE notifications SET already_read = 1 WHERE user_id = :userId");
            $query->bindParam(":userId", $userId);
            $query->execute();
        }
        return $notifications;
    }

    /**
     * Creates a notification in the database
     * @param $con PDO object
     * @param $body string | null description text
     * @param $image string | null image url
     * @param $authorId integer | null user whose action triggered this notification
     * @param $userId integer recipient user
     * @param $title string
     * @param $imageLink string | null URL used when the image is clicked
     */
    public static function addNotification($con, $body, $image, $authorId, $userId, $title, $imageLink)
    {
        $query = $con->prepare("INSERT INTO notifications(body, image, author_id, user_id, title, image_link, already_read, created_at)
                            VALUES(:body, :image, :authorId, :userId, :title,:imageLink, 0,NOW())");
        $query->bindParam(":body", $body);
        $query->bindParam(":image", $image);
        $query->bindParam(":authorId", $authorId);
        $query->bindParam(":userId", $userId);
        $query->bindParam(":title", $title);
        $query->bindParam(":imageLink", $imageLink);

        $query->execute();
    }

    /**
     * Sends text as email to user
     * @param $subject string
     * @param $body string message
     * @param $email string destination email
     */
    public static function sendEmailNotification($subject, $body, $email)
    {
        $headers = "From: ELegendz <noreply@elegendz.net>" . "\r\n" . "Reply-To: noreply@elegendz.net" . "\r\n" . "X-Mailer: PHP/" . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $wasSuccessful = mail($email, "[ELEGENDZ] $subject", $body, $headers);
    }

}