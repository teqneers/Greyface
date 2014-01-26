<?php

class BlacklistDomainStore extends AbstractStore {

    private $dbFields = array();
    private $tableName = "optin_domain";

    public function __construct(){
        $this->dbFields = array(
            "domain",
        );
    }

    public function getDomains($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    public function addDomain($domain) {
        $insertQuery =  "INSERT INTO optin_domain".
            " (domain)".
            " VALUES ('".self::$db->quote($domain)."')";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    public function deleteDomain($domain) {
        $deleteQuery =  "DELETE FROM optin_domain"
            ." WHERE domain='".self::$db->quote($domain)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

    public function updateDomain($oldDomain, $newDomain) {
        $updateQuery = "UPDATE optin_domain"
            ." SET domain='".self::$db->quote($newDomain)."'"
            ." WHERE domain='".self::$db->quote($oldDomain)."'";
        $affectedRows = self::$db->queryAffect($updateQuery);
        if($affectedRows > 0) {
            return new AjaxResult(true, "Data has been updated!");
        } else {
            return new AjaxResult(false, "The data you specified was not there. Updated 0 entries!");
        }
    }
} 