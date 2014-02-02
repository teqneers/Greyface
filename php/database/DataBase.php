<?php
// Get Config
require_once "../../php/Config.php";

/**
 * DataBase
 * This class represents the connection and interface to the database
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

            // if unhandled
            //@TODO: set if/how exceptions have to be displayed in config...
            echo new AjaxResult(
                false,
                $exception->getDBExceptionOccuredMessage()."\n"
                .$exception->getErrorNr()."\n"
                .$exception->getErrorMsg()."\n"
                .$exception->getQuery()
            );
            throw $exception;


		}

		return $result;
	}

    /**
     * Executes the query and gives back the number of affected rows
     * 
     * @param string - $query - The SQL query which shall be executed.
     * @return int - The number of affected rows of the given query.
     */
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
     * Escapes the incoming value to make provided values save for use in sql-querys!
     *
     * @param String $value - The value to quote.
     * @return String - The quoted value.
     */
    public function quote($value) {
        return self::$mysqli->real_escape_string($value);
    }
}