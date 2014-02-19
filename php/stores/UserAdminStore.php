<?php

/**
 * Class UserAdminStore
 * Store to manage user entries in database.
 */
class UserAdminStore extends AbstractStore {

    /**
     * Configuration which db fields have to be selected
     * @var array
     */
    private $dbFields = array();

    /**
     * Configuration which table have to be used for selection
     * @var string
     */
    private $tableName = "tq_user";

    public function __construct(){
        $this->dbFields = array(
            "username",
            "email",
            "is_admin",
            "user_id"
        );
    }

    /**
     * Gets the user list
     *
     * @param int - $limit - How much entries to show
     * @param int- $start - at which entry number the selection will start
     * @param string - $sortProperty after which column the selection should be sorted
     * @param string - $sortDirection - ASC or DESC
     * @param array - $filters - an array with filter options
     * @return AjaxRowsResult
     */
    public function getUsers($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    /**
     * Adds a user to the database
     *
     * @param $username
     * @param $email
     * @param $password
     * @param $isAdmin
     * @param $isRandomizePassword
     * @param $isSendEmail
     * @return AjaxResult
     */
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
            " VALUES ('".self::$db->quote($username)."','".self::$db->quote($email)."','".self::$db->quote(User::encryptPassword($password))."','$isAdmin')";
        self::$db->query($insertQuery);

        // Sends Email
        if ($isSendEmail && Config::getInstance()->isSendMail()) {
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

    /**
     * Deletes a user from the database
     * and delete all associated aliases with the user
     *
     * @param $username
     * @return AjaxResult
     */
    public function deleteUser($username) {
        // First check if the users exists and get his user_id
        $selectUserQuery = "SELECT user_id
                              FROM tq_user
                             WHERE username = '" . self::$db->quote($username) ."'";
        $result = self::$db->queryArray($selectUserQuery);

        // Decide if user exists or not
        if ( sizeof($result) > 0 ) {
            // Get user id from result
            $userId = self::$db->queryArray($selectUserQuery)[0]['user_id'];

            // Delete the user
            $deleteUserQuery =  "DELETE FROM tq_user"
                ." WHERE user_id ='".self::$db->quote($userId)."'";
            self::$db->query($deleteUserQuery);

            // Delete all associated aliases
            $deleteAliasQuery =  "DELETE FROM tq_alias"
                ." WHERE user_id='".self::$db->quote($userId)."'";
            self::$db->query($deleteAliasQuery);

            return new AjaxResult(true, "User has been removed from database!");
        }
        return new AjaxResult(false, "User does not exist in database!");
	}

    /**
     * Alters a user in the database
     *
     * @param $user_id
     * @param $username
     * @param $email
     * @param $isAdmin
     * @return AjaxResult
     */
    public function updateUser($user_id, $username, $email, $isAdmin) {
        $updateQuery =  "UPDATE tq_user"
                            ." SET username='".self::$db->quote($username)."'"
                            .", email='".self::$db->quote($email)."'"
                            .", is_admin='".self::$db->quote($isAdmin)."'"
                        ." WHERE user_id='".self::$db->quote($user_id)."'";

        $affectedRows = self::$db->queryAffect($updateQuery);
        if($affectedRows > 0) {
            return new AjaxResult(true, "User has been updated!");
        } else {
            return new AjaxResult(false, "The user you specified was not there. Updated 0 entries!");
        }

    }

    /**
    * Gets all usernames (prepended with show unassigned & show all option) for displaying in the user filter in the greylist store
    * @return AjaxRowsResult
    */
    public function getGreylistUserFilterOptions() {
        $selectQuery = "SELECT username, user_id FROM tq_user ORDER BY username ASC";
        $result = self::$db->queryArray($selectQuery);

        $result = new AjaxRowsResult($result, count($result));
        $result->prependRow(array("username"=>"show unassigned","user_id"=>"show_unassigned"));
        $result->prependRow(array("username"=>"show all","user_id"=>"show_all"));
        return $result;
    }


}