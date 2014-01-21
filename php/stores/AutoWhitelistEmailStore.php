<?php

class AutoWhitelistEmailStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "from_awl";

    public function __construct(){
        $this->dbFields = array(
            "sender_name",
            "sender_domain",
            "src",
            "first_seen",
            "last_seen"
        );
    }

    /**
     * Gets the emails from the Whitelist store [table: from_awl]
     * @param $limit
     * @param $start
     * @param null $sortProperty
     * @param null $sortDirection
     * @param array $filters
     * @return AjaxRowsResult
     */
    public function getEmails($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        // Because of the Error of Greytool we have to delete the entries with the
        // sender_domain value -undef- manually.
        $deleteQuery = "DELETE FROM from_awl WHERE sender_domain='-undef-'";
        self::$db->query($deleteQuery);

        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addEmail($sender, $domain, $source) {
        $insertQuery =  "INSERT INTO from_awl".
                        " (sender_name, sender_domain, src, last_seen)".
                        " VALUES ('".self::$db->quote($sender)."', '".self::$db->quote($domain)."', '".self::$db->quote($source)."', CURRENT_TIMESTAMP)";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    public function deleteEmail($sender, $domain, $source) {
        $deleteQuery =  "DELETE FROM from_awl"
                        ." WHERE sender_name='".self::$db->quote($sender)."'"
                        ." AND sender_domain='".self::$db->quote($domain)."'"
                        ." AND src='".self::$db->quote($source)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }
}