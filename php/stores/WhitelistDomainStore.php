<?php

class WhitelistDomainStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "optout_domain";

    public function __construct(){
        $this->dbFields = array(
            "domain",
        );
    }

    public function getDomains($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addDomain($domain) {
        $insertQuery =  "INSERT INTO optout_domain".
            " (domain)".
            " VALUES ('".self::$db->quote($domain)."')";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    public function deleteDomain($domain) {
        $deleteQuery =  "DELETE FROM optout_domain"
            ." WHERE domain='".self::$db->quote($domain)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

}