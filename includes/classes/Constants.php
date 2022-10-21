<?php

class Constants
{

    public static $usernameCharacters = "Your username must be between 5 and 25 characters";
    public static $usernameTaken = "This username already exists";
    public static $usernameStrangeCharacters = "Your username may not contain strange characters";
    public static $emailsDoNotMatch = "Your emails do not match";
    public static $emailInvalid = "Please enter a valid email address";
    public static $emailTaken = "This email is already in use";
    public static $passwordsDoNotMatch = "Your passwords do not match";
    public static $passwordNotAlphanumeric = "Your password can only contain letters and numbers";
    public static $passwordLength = "Your password must be between 5 and 30 characters";

    public static $loginFailed = "Your username or password was incorrect";
    public static $invalidZipcode = "Not a valid USA zip code";

    public static $unrecognizedImageType = "Unable to determine image type of uploaded file";
    public static $invalidImageType = "Not a gif/jpeg/png";
    public static $imageTooBig = "Image is too big (max: 10mb)";
    public static $uploadFailed = "Upload failed!";

    public static $passwordIncorrect = "Incorrect old password";
    public static $passwordCharactersInvalid = "Password includes invalid characters";

    public static $emailNotFound = "Email address not found!";
    public static $emailAddressNotConfirmed = "Your e-mail address has not been confirmed!<br/>A new mail has been sent.";

    public static $zipcodeRequiredWithAddress = "The zipcode is required if a street address is entered";
}

?>