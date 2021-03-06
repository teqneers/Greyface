<?php

/**
 * Class UserAliasStore
 * Store to manage user/alias entries in database.
 */
class UserAliasStore extends AbstractStore {

    /**
     * Configuration how filter properties have to be mapped in real table names
     * @var array
     */
    private $filterMapping = array();

    public function __construct(){
        $this->filterMapping = array(
            "email" => "tq_alias.alias_name ",
            "username" => "tq_user.username",
            "user_id" => "tq_user.user_id"
        );
    }

    /**
     * Gets the alias list
     *
     * @param int - $limit - How much entries to show
     * @param int- $start - at which entry number the selection will start
     * @param string - $sortProperty after which column the selection should be sorted
     * @param string - $sortDirection - ASC or DESC
     * @param array - $filters - an array with filter options
     * @return AjaxRowsResult
     */
    public function getAliases($limit, $start, $sortProperty, $sortDirection, $filters) {

        $filters = $this->mapFilters($filters);

        // Prepare empty statement strings
        $selectStatement = "";
        $fromStatement = "";
        $whereStatement = "";
        $orderByStatement = "";
        $limitStatement = "";

        // Get all Whitelist Data for the Email.
        $selectStatement .= "SELECT tq_alias.alias_name AS email, ".
            "tq_alias.alias_id AS alias_id, ".
//            "tq_user.user_id AS user_id, ".
            "tq_user.username AS username";

        $fromStatement .= " FROM tq_alias ".
            "LEFT JOIN tq_user ".
            "ON tq_alias.user_id = tq_user.user_id";

        // Builds WHERE clause, based on the $filters[] array.
        $count=0;
        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                if($column == "tq_user.user_id") {
                    continue;
                } else {
                    if($count > 0) {
                        $whereStatement .= "OR ";
                    } elseif ($count == 0) {
                        $whereStatement .= " WHERE ";
                    }
                    $whereStatement .= self::$db->quote($column)." LIKE '".self::$db->quote($value)."' ";
                }
            }

            foreach($filters as $column => $value) {
                if($column == "tq_user.user_id") {
                    (empty($whereStatement)) ? $whereStatement .= " WHERE tq_user.user_id = '".self::$db->quote($value)."'" : " AND tq_user.user_id = '".self::$db->quote($value)."'";
                }
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

        // Determine the total rows number (respect WHERE-statement).
        $rowNumber = DataBase::getInstance()->queryArray("SELECT COUNT(*) as nr" . $fromStatement . $whereStatement)[0]["nr"];

        return new AjaxRowsResult($result, $rowNumber);
    }

    /**
     * Function to map the filters as configured in the $filterMapping array
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
     * Adds an alias to the database
     *
     * @param $username
     * @param $alias
     * @return AjaxResult
     */
    public function addAlias($username, $alias) {
        // Emables the functionality to inser more that one alias!
        // The aliases are concatenated by # -> so we have to explode it here.
        $aliases = explode('#', $alias);

        // first we have to find the userId to the given username!
        $user = User::getUserByName($username);
        if($user->isUserExisting()) {
            $userId = $user->getUserId();
            $insertQuery =  "INSERT INTO tq_alias".
                " (user_id, alias_name) VALUES ";
            foreach($aliases as $key => $alias) {
                if ($key != 0) {
                    $insertQuery .= ',';
                }
                $insertQuery .= " ('".self::$db->quote($userId)."','".self::$db->quote($alias)."')";
            }

            self::$db->query($insertQuery);

            return new AjaxResult(true, "Alias has been added to database!");
        } else {
            return new AjaxResult(false, "Given username does not exist!");
        }
    }

    /**
     * Deletes an alias from the database
     *
     * @param $aliasId
     * @return AjaxResult
     */
    public function deleteAlias($aliasId) {
        $deleteQuery =  "DELETE FROM tq_alias"
            ." WHERE alias_id='".self::$db->quote($aliasId)."'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Alias has been removed from database!");
    }

    /**
     * Alters an alias from the database
     *
     * @param $aliasId
     * @param $aliasEmail
     * @param $username
     * @return AjaxResult
     */
    public function updateAlias($aliasId, $aliasEmail, $username) {

        // First checks if the given username exists.
        $selectQuery = "SELECT user_id FROM tq_user WHERE username='".self::$db->quote($username)."'";
        $result = self::$db->queryArray($selectQuery);
        if( count($result[0]) < 1 ) {
            // Cancel execution. The username does not exist!
            return new AjaxResult(false, "Given username does not exist!");
        }

        $updateQuery =  "Update tq_alias"
                        ." SET alias_name='".self::$db->quote($aliasEmail)."'"
                        ." ,user_id='".self::$db->quote($result[0]['user_id'])."'"
                        ." WHERE alias_id='".self::$db->quote($aliasId)."'";

        $affectedRows = self::$db->queryAffect($updateQuery);
        if($affectedRows > 0) {
            return new AjaxResult(true, "Alias has been updated!");
        } else {
            return new AjaxResult(false, "The data you specified was not there. Updated 0 entries!");
        }
    }

    /**
     * Gets all usernames (prepended with show all option) for displaying in the user filter in the alias store
     * @return AjaxRowsResult
     */
    public function getUserAliasFilterOptions() {
        $selectQuery = "SELECT username, user_id FROM tq_user ORDER BY username ASC";
        $result = self::$db->queryArray($selectQuery);

        $result = new AjaxRowsResult($result, count($result));
        $result->prependRow(array("username"=>"show all","user_id"=>"show_all"));
        return $result;
    }

    /**
     * Gets a simple username list, without any other options
     * @return AjaxRowsResult
     */
    public function getUserList() {
        $selectQuery = "SELECT username, user_id FROM tq_user ORDER BY username ASC";
        $result = self::$db->queryArray($selectQuery);

        return new AjaxRowsResult($result, count($result));
    }
}