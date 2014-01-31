<?php

class UpdateUserPasswordFilterPost extends AbstractPostAjaxRequestFilter {

    private $username = null;
    private $password = null;

    public function __construct() {
        $this->parseRequest();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    // Checks and Validates the tupel.
    public function isComplete() {
        if ( !empty($this->username) && !empty($this->password) ) {
                return true;
        }
        return false;
    }

    public function parseRequest() {
        $this->username = array_key_exists('username', $_POST) ? $_POST['username'] : null ;
        $this->password = array_key_exists("password", $_POST) ? $_POST['password'] : null;
    }

}