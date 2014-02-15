<?php

/**
 * Class AbstractStore
 * An abstract store class to manage database tables.
 * Can be implemented by specific store classes.
 * This class provides a standard get/read method which supports filtering, sorting, paging
 */
abstract class AbstractStore {

    /**
     * Instances of derived child stores for singleton mechanism
     * @var array
     */
    private static $instances = array();

    /**
     * @var DataBase/Instance
     */
    protected static $db = null;

    /**
     * The abstract inherited getInstance method of Singleton objects.
     *
     * @return AbstractStore
     */
    final public static function getInstance()
    {
        // Ensures that there is a DataBase
        if(empty(self::$db)) {
            self::$db = DataBase::getInstance();
        }

        $class = get_called_class();                        // Gets the classname
        if(empty(self::$instances[$class])) {               // Look if there is already an instance of this class
            $rc = new ReflectionClass($class);              // ReflectionClass represents the Class which is submitted in the $class variable
            self::$instances[$class] = $rc->newInstance();  // Calls the constructor of the ReflectionClass of type $class. A new instance is born.
        }
            return self::$instances[$class];                // Returns the requested instance.
    }

    /**
     * Overwrite due to singleton pattern
     */
    final private function __clone() {
        // SINGLETONS cannot be cloned.
    }

    /**
     * Make a selection to the database
     *
     * @param $table - the table which should be selected
     * @param $columns - the columns which should be selected
     * @param $limit - limits the selection (default 100)
     * @param $start - where the selection should start
     * @param null $sortProperty - after which column the selection will be sorted
     * @param null $sortDirection - in which direction (ASC, DESC) the selection should be sorted
     * @param array $filters - filter which will be applied to the selection (column => property)
     * @return AjaxRowsResult - The result with the total row number for pagination
     */
    final protected function getData($table, $columns, $limit, $start, $sortProperty=NULL, $sortDirection=NULL, $filters=array()) {
        // Prepare empty statement strings
        $selectFromStatement="";
        $whereStatement = "";
        $orderByStatement = "";
        $limitStatement = "";

        // Build SELECT FROM.
        $selectFromStatement .= "SELECT ".$columns." FROM " . $table;

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
        $query = $selectFromStatement . $whereStatement . $orderByStatement . $limitStatement;
        $result = self::$db->queryArray($query);

        // Determine the total rows number (respect WHERE-statement).
        $rowNumber = DataBase::getInstance()->queryArray("SELECT COUNT(*) as nr FROM " . $table . $whereStatement)[0]["nr"];

        return new AjaxRowsResult($result, $rowNumber);
    }

}



