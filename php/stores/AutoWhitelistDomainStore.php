<?php

class AutoWhitelistDomainStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "domain_awl";

    public function __construct(){
        $this->dbFields = array(
            "sender_domain",
            "src",
            "first_seen",
            "last_seen",
        );
    }

    public function getDomains($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addDomain($domain, $source) {
        $insertQuery =  "INSERT INTO domain_awl".
            " (sender_domain, src, last_seen)".
            " VALUES ('$domain', '$source', CURRENT_TIMESTAMP)";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    public function deleteDomain($domain, $source) {
        $deleteQuery =  "DELETE FROM domain_awl"
            ." WHERE sender_domain='$domain'"
            ." AND src='$source'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }
}