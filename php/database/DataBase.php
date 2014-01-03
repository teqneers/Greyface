<?php
// Get Config
require_once "../../php/Config.php";

/**
 * DataBase
 * This class is the connection to the database
 */
class DataBase {

    /**
     * @var Singleton instance
     */
    private static $instance = null;

    /**
     * @var instance of mysqli
     */
    private static $mysqli = null;



    /**
     * Private __constructor due to singleton pattern
     * Connects to database
     */
    private function __construct(){
        // Connect...
        self::$mysqli = new mysqli(
            Config::getInstance()->getHostname(),
            Config::getInstance()->getDbUsername(),
            Config::getInstance()->getDbPassword(),
            Config::getInstance()->getDbName()
        );
        if (mysqli_connect_error()) {
            die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }
    }

    /**
     * Private __clone due to singleton pattern
     */
    private function __clone(){
        // Empty due to singleton pattern.
    }

    /**
     * Singleton getInstance method.
     * @return DataBase|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Sets up a query to the database
     *
     * @param String $query (SQL statement)
     * @return MySQLi_Result the result from the database
     * @throws DbQueryException
     */
    public function query($query) {
		$result	= self::$mysqli->query($query);
		if( self::$mysqli->error ) {
			$exception = new DBException(self::$mysqli->error, self::$mysqli->errno, $query);
            if($exception->isDuplicateKeyError()) {
                echo new AjaxResult(false, $exception->getDuplicateKeyMessage());
                exit;
            }
            throw $exception;
		}

		return $result;
	}

    public function queryAffect($query) {
        $result = $this->query($query);
        return self::$mysqli->affected_rows;
    }

    /**
     * Sets up a query to the database and gets the result as array.
     *
     * @param String $query - The sql-query
     * @return array - The result
     */
    public function queryArray($query) {
        try {
            $result = $this->query($query);
            $array = array();
            while($row =  $result->fetch_assoc() ) {
                $array[]	=	$row;
            }
            return $array;
        } catch (Exception $e) {
            print_r($e);
            return null;
        }
    }

    /**
     * Sets up a query to the database and gets the result as object[].
     *
     * @param String $query - The sql-query
     * @return Array - The result as object-array
     */
    public function queryObjects($query) {
        $array	= array();
        $result	= $this->query($query);
        while($obj = $result->fetch_object() ) {
            $array[]	=	$obj;
        }

        return $array;
    }

    /**
     * Gets the total amount of rows of the given table.
     *
     * @param String $tableName - The name of the table you will count
     * @return int
     */
    public function getTotal($tableName)
    {
        $rowsTotal = $this->queryArray("SELECT COUNT(*) as nr FROM ". $tableName);
        return $rowsTotal[0]["nr"];
    }

    /**
     * Checks the incoming value and escape the value to make an inserted db-query-string save!
     *
     * @param String $value - The value to quote.
     * @return String - The quoted value.
     */
    public function quote($value) {
        return self::$mysqli->real_escape_string($value);
    }
}