<?php

/**
 * Class BlacklistEmailStore
 * Store to manage the Blacklist/emails in database
 */
class BlacklistEmailStore extends AbstractStore {

    /**
     * Configuration which db fields have to be selected
     * @var array
     */
    private $dbFields = array();

    /**
     * Configuration which table have to be used for selection
     * @var string
     */
    private $tableName = "optin_email";

    public function __construct(){
        $this->dbFields = array(
            "email",
        );
    }

    /**
     * Gets the blacklist list
     *
     * @param int - $limit - How much entries to show
     * @param int- $start - at which entry number the selection will start
     * @param string - $sortProperty after which column the selection should be sorted
     * @param string - $sortDirection - ASC or DESC
     * @param array - $filters - an array with filter options
     * @return AjaxRowsResult
     */
    public function getEmails($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    /**
     * Adds an email to database
     * @param $email
     * @return AjaxResult
     */
    public function addEmail($email) {
        $insertQuery =  "INSERT INTO optin_email".
            " (email)".
            " VALUES ('".self::$db->quote($email)."')";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    /**
     * Deletes an email from database
     * @param $email
     * @return AjaxResult
     */
    public function deleteEmail($email) {
        $deleteQuery =  "DELETE FROM optin_email"
            ." WHERE email='".self::$db->quote($email)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

    /**
     * Alters an email from database
     * @param $oldEmail
     * @param $newEmail
     * @return AjaxResult
     */
    public function updateEmail($oldEmail, $newEmail) {
        $updateQuery = "UPDATE optin_email"
                        ." SET email='".self::$db->quote($newEmail)."'"
                        ." WHERE email='".self::$db->quote($oldEmail)."'";
        $affectedRows = self::$db->queryAffect($updateQuery);
        if($affectedRows > 0) {
            return new AjaxResult(true, "Data has been updated!");
        } else {
            return new AjaxResult(false, "The data you specified was not there. Updated 0 entries!");
        }
    }
}