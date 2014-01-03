<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class DeleteGreyfaceEntriesToFilter {

    private $year = null;
    private $month = null;
    private $day = null;

    /**
     * @var Singleton instance
     */
    private static $instance = null;

    /**
     * Private __constructor due to singleton pattern
     */
    private function __construct() {
        $this->parseRequest();
    }

    /**
     * Private __clone due to singleton pattern
     */
    private function __clone(){
        // Empty due to singleton pattern.
    }

    /**
     * Singleton getInstance method.
     * @return DeleteGreyfaceEntriesToFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }


    public function getYear()
    {
        return $this->year;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function isDateComplete() {
        if ( !empty($this->year) && !empty($this->month) && !empty($this->day) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int - timestamp
     */
    public function getTimestamp() {
        $dateTime = $this->getDateTime();
        return $dateTime->getTimestamp();
    }

    /**
     * @return DateTime
     */
    public function getDateTime() {
        $dateTime = new DateTime();
        if($this->isDateComplete()) {
            $dateTime->setDate($this->year , $this->month , $this->day);
            $dateTime->setTime(0,0,0);
            return $dateTime;
        } else {
            $dateTime->setDate(0, 0, 0);
            $dateTime->setTime(0,0,0);
            return null;
        }
    }

    public function parseRequest() {
        $this->year	= array_key_exists('toYear', $_GET) ? (int) $_GET['toYear'] : null ;
        $this->month = array_key_exists("toMonth", $_GET) ? (int) $_GET['toMonth'] : null;
        $this->day= array_key_exists("toDay", $_GET) ? (int) $_GET['toDay'] : null;
    }

}