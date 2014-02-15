<?php

/**
 * Class WhitelistDomainStore
 * Store to manage Whitelist/domain entries in database.
 */
class WhitelistDomainStore extends AbstractStore {

    /**
     * Configuration which db fields have to be selected
     * @var array
     */
    private $dbFields = array();

    /**
     * Configuration which table have to be used for selection
     * @var string
     */
    private $tableName = "optout_domain";

    public function __construct(){
        $this->dbFields = array(
            "domain",
        );
    }

    /**
     * Gets the domain list
     *
     * @param int - $limit - How much entries to show
     * @param int- $start - at which entry number the selection will start
     * @param string - $sortProperty after which column the selection should be sorted
     * @param string - $sortDirection - ASC or DESC
     * @param array - $filters - an array with filter options
     * @return AjaxRowsResult
     */
    public function getDomains($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    /**
     * Adds an entry to the table
     *
     * @param $domain
     * @return AjaxResult
     */
    public function addDomain($domain) {
        $insertQuery =  "INSERT INTO optout_domain".
            " (domain)".
            " VALUES ('".self::$db->quote($domain)."')";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    /**
     * Deletes an entry from the table
     *
     * @param $domain
     * @return AjaxResult
     */
    public function deleteDomain($domain) {
        $deleteQuery =  "DELETE FROM optout_domain"
            ." WHERE domain='".self::$db->quote($domain)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

    /**
     * Alters an entry in the table
     *
     * @param $oldDomain
     * @param $newDomain
     * @return AjaxResult
     */
    public function updateDomain($oldDomain, $newDomain) {
        $updateQuery = "UPDATE optout_domain"
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