<?php

/**
 * Class GreylistStore
 * Access to the greylist-table (connect)
 */
class GreylistStore extends AbstractStore {

    /**
     * The user which is logged in
     * @var User instance
     */
    private $user = null;

    /**
     * Filter propertys which will be blacklisted
     * @var array
     */
    private $filterBlacklist = array();

    /**
     * Configuration how filter properties have to be mapped in real table names
     * @var array
     */
    private $filterMapping = array();

    public function __construct(){
        $this->user = Login::getInstance()->getLoginResult()->getUser();
        $this->filterBlacklist = array(
            "user_id",
        );
        $this->filterMapping = array(
            "sender_name" => "connect.sender_name",
            "sender_domain" => "connect.sender_domain",
            "source" => "connect.src",
            "recipient" => "connect.rcpt",
            "first_seen" => "connect.first_seen",
            "alias_name" => "tq_alias.alias_name",
            "username" => "tq_user.username",
            "user_id" => "tq_user.user_id"
        );
    }

    /**
     * Gets the greylist, filtered and sorted by the given directives.
     *
     * @param $limit
     * @param $start
     * @param $sortProperty
     * @param $sortDirection
     * @param $filters
     * @return AjaxRowsResult
     */
    public function getGreylist($limit, $start, $sortProperty, $sortDirection, $filters) {

        $filters = $this->mapFilters($filters);

        // Prepare empty statement strings
        $selectStatement = "";
        $fromStatement = "";
        $whereStatement = "";
        $orderByStatement = "";
        $limitStatement = "";

        // Get all Greylist data for the Email.
        $selectStatement .= "SELECT connect.sender_name AS sender_name, ".
                                   "connect.sender_domain AS sender_domain, ".
                                   "connect.src AS source, ".
                                   "connect.rcpt AS recipient, ".
                                   "connect.first_seen AS first_seen, ".
                                   "tq_alias.alias_name AS alias_name, ".
                                   "tq_user.username AS username";
        $fromStatement .= " FROM connect ".
                           "LEFT JOIN tq_alias ".
                           "ON tq_alias.alias_name = connect.rcpt ".
                           "LEFT JOIN tq_user ".
                           "ON tq_user.email = connect.rcpt || tq_user.user_id = tq_alias.user_id";

        // Builds WHERE clause, based on the $filters[] array.
        if(count($filters) > 0) {
            $count = 0;
            foreach($filters as $column => $value) {
                if ($column == "tq_user.user_id") {
                    continue;
                } else {
                    if($count > 0) {
                        $whereStatement .= "OR ";
                    } elseif ($count == 0) {
                        $whereStatement .= " WHERE ";
                    }
                    $whereStatement .= self::$db->quote($column)." LIKE '".self::$db->quote($value)."' ";
                }
                $count++;

            }
            // check if "show_unassigned" filter is set!
            foreach($filters as $column => $value) {
                if ( $column == "tq_user.user_id" && $value == "show_unassigned" ) {
                    empty($whereStatement)
                        ? $whereStatement .= " WHERE tq_user.username IS NULL "
                        : $whereStatement .= " OR tq_user.username = IS NULL " ;
                } elseif ($column == "tq_user.user_id") {
                    empty($whereStatement)
                        ? $whereStatement .= " WHERE tq_user.user_id = '".self::$db->quote($value)."'"
                        : $whereStatement .= " AND tq_user.user_id = '".self::$db->quote($value)."'";
                }
            }
        }
        // For non admin users - their user id will be pushed in the WHERE clause
        if(!$this->user->isAdmin()) {
            if(empty($whereStatement)) {
                $whereStatement .= " WHERE (tq_user.user_id = ".$this->user->getUserId()." OR tq_alias.user_id = ".$this->user->getUserId().")";
            } else {
                $whereStatement .= " AND (tq_user.user_id = ".$this->user->getUserId()." OR tq_alias.user_id = ".$this->user->getUserId().")";
            }
        }


        // Builds ORDER BY clause, based on the sort options $sortProperty and $sortDirection.
        if(isset($sortProperty) && isset($sortDirection)) {
            $orderByStatement .= " ORDER BY $sortProperty $sortDirection";
        }

        // Adds the LIMIT clause at the end of SQL-statement.
        $limitStatement .=	" LIMIT ".self::$db->quote($start).",".self::$db->quote($limit);

        // Build Statement and send it to database.
        $query = $selectStatement . $fromStatement . $whereStatement . $orderByStatement . $limitStatement;
        $result = self::$db->queryArray($query);

        // First we get all users from table 'tq_user'
        $userQuery = 'SELECT tq_alias.alias_name, tq_user.username
                        FROM tq_alias
                   LEFT JOIN tq_user ON tq_alias.user_id = tq_user.user_id';
        $users = self::$db->queryArray($userQuery);

        // For performance reasons we create an key->value (email -> username) array, which we will use to find users in future steps
        $userArray = array();
        foreach($users as $user) {
            $userArray[ strtolower($user['alias_name']) ] = $user['username'];
        }
        unset($users);

        // Now we go through the result greylist, look for empty usernames, and try to look them up in the user list we fetched just above!
        foreach($result as &$itemToProcess) {
            if( empty($itemToProcess['username']) ) {
                if ( array_key_exists( strtolower($itemToProcess['recipient']), $userArray) ) {
                    $itemToProcess['username'] = $userArray[strtolower($itemToProcess['recipient'])];
                } else {
                    $itemToProcess['username'] = '---';
                }
            }
        }
        ################################################################################################################

        // Determine the total rows number (respect WHERE-statement).
        $rowNumber = DataBase::getInstance()->queryArray("SELECT COUNT(*) as nr" . $fromStatement . $whereStatement)[0]["nr"];
        return new AjaxRowsResult($result, $rowNumber);
    }

    /**
     * Maps filter-property names to the ones defined in the filterMapping[] array in this class
     *
     * @param $filters
     * @return array
     */
    private function mapFilters($filters) {
        $mappedFilters = array();
        foreach($filters as $property => $value) {
            if(array_key_exists($property, $this->filterMapping)) {
                $mappedFilters[$this->filterMapping[$property]] = $value;
            } else {
                $mappedFilters[$property] = $value;
            }
        }
        return $mappedFilters;
    }

    /**
     * Deletes all entrys from past to the given date.
     *
     * @param $dateTime
     * @return AjaxResult
     */
    public function deleteTo($dateTime) {
        $delete = "DELETE FROM connect WHERE first_seen < '" . self::$db->quote($dateTime->format("Y-m-d"))."'";
        self::$db->query($delete);
        return new AjaxResult(true, "Data have been removed from database!");
    }

    /**
     * Deletes given entry from greylist.
     *
     * @param $senderName
     * @param $senderDomain
     * @param $src
     * @param $rcpt
     * @return AjaxResult
     */
    public function delete($senderName, $senderDomain, $src, $rcpt) {
        $delete = "DELETE FROM connect".
                  " WHERE sender_name='".self::$db->quote($senderName)."'".
				  " AND sender_domain='".self::$db->quote($senderDomain)."'".
				  " AND src='".self::$db->quote($src)."'".
				  " AND rcpt='".self::$db->quote($rcpt)."'" ;
        self::$db->query($delete);
        return new AjaxResult(true, "Data have been removed from database!");
    }

    /**
     * Moves given entry from greylist to whitelist.
     *
     * @param $senderName
     * @param $senderDomain
     * @param $src
     * @param $rcpt
     * @return AjaxResult
     */
    public function toWhitelist($senderName, $senderDomain,$src,$rcpt) {
        $queryWhitelist	= "SELECT COUNT(*) AS nr FROM from_awl".
                            " WHERE sender_name = '".self::$db->quote($senderName)."'".
                            " AND sender_domain = '".self::$db->quote($senderDomain)."'".
                            " AND src = '".self::$db->quote($src)."'";

        $isAlreadyInWhitelist = self::$db->queryArray($queryWhitelist)[0]["nr"];
        if($isAlreadyInWhitelist == 0) {
            $queryGreylist = "SELECT * FROM connect".
                                " WHERE sender_name='".self::$db->quote($senderName)."'".
                                " AND sender_domain='".self::$db->quote($senderDomain)."'".
                                " AND src='".self::$db->quote($src)."'".
                                " AND rcpt='".self::$db->quote($rcpt)."'".
                                " ORDER BY first_seen DESC";

            $entry = self::$db->queryArray($queryGreylist)[0];
            if(isset($entry)) {
                $insertWhitelist = "INSERT INTO from_awl(sender_name, sender_domain, src, first_seen, last_seen)".
                    " Values('".$entry["sender_name"]."','".$entry["sender_domain"]."','".$entry["src"]."','".$entry["first_seen"]."','".$entry["first_seen"]."')";
                self::$db->query($insertWhitelist);
            }
            // delete the entry in the greylist
            $this->delete($senderName,$senderDomain,$src,$rcpt);
        }
        return new AjaxResult(true, "Data have been moved to whitelist!");

    }
}