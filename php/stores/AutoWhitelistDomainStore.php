<?php

/**
 * Class AutoWhitelistDomainStore
 * Store to manage the AutoWhitelist/emails in database
 */
class AutoWhitelistDomainStore extends AbstractStore {

    /**
     * Configuration which db fields have to be selected
     * @var array
     */
    private $dbFields = array();

    /**
     * Configuration which table have to be used for selection
     * @var string
     */
    private $tableName = "domain_awl";

    public function __construct(){
        $this->dbFields = array(
            "sender_domain",
            "src",
            "first_seen",
            "last_seen",
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
     * Adds a domain to database
     *
     * @param $domain
     * @param $source
     * @return AjaxResult
     */
    public function addDomain($domain, $source) {
        $insertQuery =  "INSERT INTO domain_awl".
            " (sender_domain, src, last_seen)".
            " VALUES ('".self::$db->quote($domain)."', '".self::$db->quote($source)."', CURRENT_TIMESTAMP)";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    /**
     * Deletes domain from database
     * @param $domain
     * @param $source
     * @return AjaxResult
     */
    public function deleteDomain($domain, $source) {
        $deleteQuery =  "DELETE FROM domain_awl"
            ." WHERE sender_domain='".self::$db->quote($domain)."'"
            ." AND src='".$source."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

    /**
     * Alters domain from database
     *
     * @param $senderDomain
     * @param $src
     * @param $senderDomainId
     * @param $srcId
     * @return AjaxResult
     */
    public function updateDomain($senderDomain, $src, $senderDomainId, $srcId) {
        $updateQuery =  "UPDATE domain_awl"
            ." SET sender_domain='".self::$db->quote($senderDomain)."'"
            .", src='".self::$db->quote($src)."'"
            ." WHERE sender_domain='".self::$db->quote($senderDomainId)."'"
            ." AND src='".self::$db->quote($srcId)."'";

        $affectedRows = self::$db->queryAffect($updateQuery);
        if($affectedRows > 0) {
            return new AjaxResult(true, "Data has been updated!");
        } else {
            return new AjaxResult(false, "The data you specified was not there. Updated 0 entries!");
        }
    }
}