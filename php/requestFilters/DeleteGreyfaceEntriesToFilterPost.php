<?php

/*
 * This class tries to get the POST parameters of a delete-greyface-entries request.
 */
class DeleteGreyfaceEntriesToFilterPost extends AbstractPostAjaxRequestFilter {

    private $year = null;
    private $month = null;
    private $day = null;

    public function __construct() {
        $this->parseRequest();
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
        $dateTime = new DateTime("now", new DateTimeZone("UTC"));
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
        $this->year	= array_key_exists('toYear', $_POST) ? (int) $_POST['toYear'] : null ;
        $this->month = array_key_exists("toMonth", $_POST) ? (int) $_POST['toMonth'] : null;
        $this->day= array_key_exists("toDay", $_POST) ? (int) $_POST['toDay'] : null;
    }

}