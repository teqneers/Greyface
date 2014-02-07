<?php
/**
 * Class DBException
 *
 * This class represents any Exception which can occurre while handling a mysql db request.
 */
class DBException extends Exception {

    /**
     * @var int - $errorNr - The mySql error nr.
     */
    private $errorNr;
    /**
     * @var string - $errorMsg - The Message to show.
     */
    private $errorMsg;
    /**
     * @var string - $query - The query which causes the error.
     */
    private $query;

    /**
     * The constructor
     *
     * @param string - $errorMsg - The Message to show.
     * @param int - $errorNr - The mySql error nr.
     * @param string - $query - The query which causes the error.
     */
    public function __construct($errorMsg, $errorNr, $query) {
        $this->errorMsg = $errorMsg;
        $this->errorNr = $errorNr;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * @return int
     */
    public function getErrorNr()
    {
        return $this->errorNr;
    }

    /**
     * @return string - failed query
     */
    public function getQuery()
    {
        return $this->query;
    }


    /**
     * @return bool - true if the mysql error nr = 1062 - Duplicate Key Entry
     */
    public function isDuplicateKeyError() {
        return ($this->errorNr == 1062) ? true : false;
    }

    /**
     * @return string - gets the Error Description for 1062:Duplicate Key Entry
     */
    public function getDuplicateKeyMessage() {
        return "Duplicate Key Entry- Data is already in the table!";
    }

    public function getDBExceptionOccuredMessage()  {
        return "An unhandled DB Exception occured.";
    }
} 