<?php
/**
 * This class represents the result of the login attempt.
 *
 * Created by PhpStorm.
 * User: svencc
 * Date: 09.12.13
 * Time: 17:14
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
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @return boolean
     */
    public function getResult()
    {
        return $this->result;
    }

    public function getResultString() {
        return ($this->result)?"true":"false";
    }

    /**
     * @return User instance or null
     */
    public function getUser()
    {
        return $this->user;
    }
} 