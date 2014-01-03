<?php

class UserAdminStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "tq_user";

    public function __construct(){
        $this->dbFields = array(
            "username",
            "email",
            "is_admin"
        );
    }

    public function getUsers($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addUser($username, $email, $password, $isAdmin, $isRandomizePassword, $isSendEmail) {

        // Sanitize parameters: $isAdmin, $isRandomizePassword, $isSendEmail
        $isSendEmail = (bool) $isSendEmail;
        $isAdmin = ($isAdmin) ? 1 : 0;
        // If $isRandomizePassword is true, we have to generate one.
        if ( (boolean) $isRandomizePassword == true ) {
            $password = User::generateRandomizedPassword(16);
        }

        // Insert to DB
        $insertQuery =  "INSERT INTO tq_user".
            " (username, email,  password, is_admin)".
            " VALUES ('$username','$email','".User::encryptPassword($password)."','$isAdmin')";
        self::$db->query($insertQuery);

        // Sends Email
        if ($isSendEmail) {
            mail(
                $email,
                "Your Greyface account",
                "Hello $username,\n\n".
                "a new Greyface account has been created for you on ".$_SERVER['HTTP_HOST'].".\n\n".
                "Your username: $username\n".
                "Your password: $password\n\n\n".
                "Greyface",
                "From: Greyface"
            );
        }

        return new AjaxResult(true, "User has been added to database!");
	}

	public function deleteUser($username) {
	    $deleteQuery =  "DELETE FROM tq_user"
            ." WHERE username='$username'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "User has been removed from database!");
	}

} 