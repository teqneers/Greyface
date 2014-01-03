<?php

class WhitelistEmailStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "optout_email";

    public function __construct(){
        $this->dbFields = array(
            "email",
        );
    }

    public function getEmails($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addEmail($email) {
        $insertQuery =  "INSERT INTO optout_email".
            " (email)".
            " VALUES ('$email')";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    public function deleteEmail($email) {
        $deleteQuery =  "DELETE FROM optout_email"
            ." WHERE email='$email'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

} 