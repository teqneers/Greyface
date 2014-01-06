<?php

class UserAliasStore extends AbstractStore {

    private $filterMapping = array();

    public function __construct(){
        $this->filterMapping = array(
            "email" => "tq_alias.alias_name ",
            "username" => "tq_user.username",
        );
    }

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
            "tq_user.user_id AS user_id, ".
            "tq_user.username AS username";

        $fromStatement .= " FROM tq_alias ".
            "LEFT JOIN tq_user ".
            "ON tq_alias.user_id = tq_user.user_id";

        // Builds WHERE clause, based on the $filters[] array.
        if(count($filters) > 0) {
            $whereStatement .= " WHERE ";
            foreach($filters as $column => $value) {
                $whereStatement .= self::$db->quote($column)." LIKE '".self::$db->quote($value)."' ";
                $whereStatement .= "OR ";
            }
            $whereStatement = substr($whereStatement, 0,-3); // Cuts off the last three "OR " characters (they are too much).
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

    public function addAlias($username, $alias) {
        // first we have to find the userId to the given username!
        $user = User::getUserByName($username);
        if($user->isUserExisting()) {
            $userId = $user->getUserId();
            $insertQuery =  "INSERT INTO tq_alias".
                " (user_id, alias_name)".
                " VALUES ('$userId','$alias')";
            self::$db->query($insertQuery);

            return new AjaxResult(true, "Alias has been added to database!");
        } else {
            return new AjaxResult(false, "Given username does not exist!");
        }
    }

    public function deleteAlias($aliasId) {
        $deleteQuery =  "DELETE FROM tq_alias"
            ." WHERE alias_id='$aliasId'";
        self::$db->query($deleteQuery);
        return new AjaxResult(true, "Alias has been removed from database!");
    }

}