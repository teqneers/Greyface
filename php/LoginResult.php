<?php
/**
 * This class represents the result of the login attempt.
 */

class LoginResult {
    private $result = false;
    private $msg = "";
    private $user = null;

    /**
     * Constructs the LoginResult
     *
     * @param boolean $result - was login attempt successfully or not
     * @param $msg - some information about login; especially on errors
     * @param $user - if successfully, attach an user instance!
     */
    public function __construct($result, $msg, $user) {
        $this->result = (boolean)$result;
        $this->msg = $msg;
        $this->user = $user;
    }

    /**
     * The message which tells some more about the login attempt
     * 
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * If the login attempt was successful or not
     * 
     * @return boolean
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * If the login attempt was successful or not (bool is represented as string)
     * 
     * @return string - "true" or "false"
     */
    public function getResultString() {
        return ($this->result)?"true":"false";
    }

    /**
     * Gets the user which is logged in
     * 
     * @return User instance or null if login attempt failed
     */
    public function getUser()
    {
        return $this->user;
    }
} 