<?php

/**
 * Class AutoWhitelistEmailStore
 * Store to manage the AutoWhitelist/emails in database
 */
class AutoWhitelistEmailStore extends AbstractStore {

    /**
     * Configuration which db fields have to be selected
     * @var array
     */
    private $dbFields = array();

    /**
     * Configuration which table have to be used for selection
     * @var string
     */
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
     * @param int - $limit - How much entries to show
     * @param int- $start - at which entry number the selection will start
     * @param string - $sortProperty after which column the selection should be sorted
     * @param string - $sortDirection - ASC or DESC
     * @param array - $filters - an array with filter options
     */
    public function getEmails($limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        // Because of the Error of Greytool we have to delete the entries with the
        // sender_domain value -undef- manually.
        $deleteQuery = "DELETE FROM from_awl WHERE sender_domain='-undef-'";
        self::$db->query($deleteQuery);

        return $this->getData($this->tableName, implode(", ", $this->dbFields), $limit, $start, $sortProperty, $sortDirection, $filters);
    }

    /**
     * Adds email to database
     * @param $sender
     * @param $domain
     * @param $source
     * @return AjaxResult
     */
    public function addEmail($sender, $domain, $source) {
        $insertQuery =  "INSERT INTO from_awl".
                        " (sender_name, sender_domain, src, last_seen)".
                        " VALUES ('".self::$db->quote($sender)."', '".self::$db->quote($domain)."', '".self::$db->quote($source)."', CURRENT_TIMESTAMP)";

        self::$db->query($insertQuery);
        return new AjaxResult(true, "Data has been added to database!");
    }

    /**
     * Deletes email from database
     * @param $sender
     * @param $domain
     * @param $source
     * @return AjaxResult
     */
    public function deleteEmail($sender, $domain, $source) {
        $deleteQuery =  "DELETE FROM from_awl"
                        ." WHERE sender_name='".self::$db->quote($sender)."'"
                        ." AND sender_domain='".self::$db->quote($domain)."'"
                        ." AND src='".self::$db->quote($source)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Data has been removed from database!");
    }

    /**
     * Alters email in database
     * @param $senderName
     * @param $senderDomain
     * @param $src
     * @param $senderNameId
     * @param $senderDomainId
     * @param $srcId
     * @return AjaxResult
     */
    public function updateEmail($senderName, $senderDomain, $src, $senderNameId, $senderDomainId, $srcId) {
        $updateQuery =  "UPDATE from_awl"
                        ." SET sender_name='".self::$db->quote($senderName)."'"
                        .", sender_domain='".self::$db->quote($senderDomain)."'"
                        .", src='".self::$db->quote($src)."'"
                        ." WHERE sender_name='".self::$db->quote($senderNameId)."'"
                        ." AND sender_domain='".self::$db->quote($senderDomainId)."'"
                        ." AND src='".self::$db->quote($srcId)."'";

        $affectedRows = self::$db->queryAffect($updateQuery);
        if($affectedRows > 0) {
            return new AjaxResult(true, "Data has been updated!");
        } else {
            return new AjaxResult(false, "The data you specified was not there. Updated 0 entries!");
        }
    }
}