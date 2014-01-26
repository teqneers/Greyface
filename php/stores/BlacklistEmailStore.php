<?php

class BlacklistEmailStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "optin_email";

    public function __construct(){
        $this->dbFields = array(
            "email",
        );
    }

    public function getEmails($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addEmail($email) {
        $insertQuery =  "INSERT INTO optin_email".
            " (email)".
            " VALUES ('".self::$db->quote($email)."')";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    public function deleteEmail($email) {
        $deleteQuery =  "DELETE FROM optin_email"
            ." WHERE email='".self::$db->quote($email)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

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