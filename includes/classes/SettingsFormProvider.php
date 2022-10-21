<?php

class SettingsFormProvider
{

    public function __construct($con)
    {
        $this->con = $con;
    }

    public function createUserDetailsForm($email, $user)
    {

        $emailInput = $this->createEmailInput($email);
        $categoriesInput = $this->createCategoriesInput($user);
        $saveButton = $this->createSaveUserDetailsButton();


        return "<form action='settings.php' method='POST' enctype='multipart/form-data'>
                    <span class='title'>User details</span>
                    $emailInput
                    $categoriesInput
                    $saveButton
                    
                </form>";
    }

    public function createPasswordForm($account)
    {
        $oldPasswordInput = $this->createPasswordInput("oldPassword", "Old password");
        $newPasswordInput = $this->createPasswordInput("newPassword", "New password");
        $newPassword2Input = $this->createPasswordInput("newPassword2", "Confirm new password");
        $saveButton = $this->createSavePasswordButton();


        return "<form method='POST'>
                <p>Fill to update your password:</p>
                    " . $account->getError(Constants::$passwordIncorrect) . "
                    $oldPasswordInput
                    " . $account->getError(Constants::$passwordsDoNotMatch) . "
                    " . $account->getError(Constants::$passwordCharactersInvalid) . "
                    " . $account->getError(Constants::$passwordLength) . "
                    $newPasswordInput
                    " . $account->getError(Constants::$passwordsDoNotMatch) . "
                    $newPassword2Input
                    <div class='mt-3'>
                    $saveButton
                    </div>
                </form>";
    }

    private function createEmailInput($value)
    {
        if ($value == null) $value = "";
        return "<div class='form-group'>

                    <input class='form-control' type='email' placeholder='Email' name='email' value='$value' required>
                </div>";
    }


    public function createCategoriesInput($user)
    {
        $query = $this->con->prepare("SELECT * FROM categories");
        $query->execute();
        $categories = $query->fetchAll(PDO::FETCH_ASSOC);
        $userCategories = $user->getCategories();
        //create dictionary
        $userCategoriesDictionary = [];
        foreach ($userCategories as $userCategory)
            $userCategoriesDictionary[$userCategory['category_id']] = $userCategory['name'];


        $html = '<div id="categories_input">
                    <p>Select how you like to Entertain or Entertainment you like:</p>
                    <div class="row">';

        $i = 0;
        foreach ($categories as $category) {
            $html .= '<div class="col-12 col-md-4">
                    <div class="form-check form-check-inline category-option">
                        <input class="form-check-input" type="checkbox" name="' . $category['id'] . '" ' . (isset($userCategoriesDictionary[$category['id']]) ? 'checked' : '') . '
                               id="category_' . $category['name'] . '" value="1"/>
                        <label class="form-check-label" for="inlineCheckbox2">' . $category['name'] . '</label>
                    </div>
                </div>';

           /* if ($i % 3 == 2)
                $html .= "<br/>";*/
            $i++;
        }
        $html .= '</div></div>';

        return $html;

    }

    private function createSaveUserDetailsButton()
    {
        return "<button type='submit' class='btn btn-primary' name='saveDetailsButton'>Save</button>";
    }

    private function createSavePasswordButton()
    {
        return "<button type='submit' class='btn btn-warning' name='savePasswordButton'>Update</button>";
    }

    private function createPasswordInput($name, $placeholder)
    {
        return "
<div class='mb-2'>
<div class='form-group'>
<label for='$name' class='form-label w-100'>$placeholder</label>
                    <input class='form-control' type='password'  name='$name' id='$name'  required>
                </div>
                </div>";
    }
}

?>