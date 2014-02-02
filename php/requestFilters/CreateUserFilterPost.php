<?php
/*
 * This class tries to get the POST parameters of a create user request.
 */
class CreateUserFilterPost extends AbstractPostAjaxRequestFilter {

    private $username = null;
    private $email = null;
    private $password = null;
    private $isAdmin = null;
    private $randomizePassword = null;
    private $sendEmail = null;

    public function __construct() {
        $this->parseRequest();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function isRandomizePassword()
    {
        return $this->randomizePassword;
    }

    public function isSendEmail()
    {
        return $this->sendEmail;
    }

    // Checks and Validates the tupel.
    // Password can only be empty if isRandomizeEmail = true
    public function isComplete() {
        if ( !empty($this->username) && !empty($this->email) ) {
            if (!empty($this->password)) {
                return true;
            } elseif( empty($this->password) && $this->randomizePassword ) {
            	return true;
            } else {
            	return false;
            }
        }
        return false;
    }

    public function parseRequest() {
        $this->username = array_key_exists('username', $_POST) ? $_POST['username'] : null ;
        $this->email = array_key_exists("email", $_POST) ? $_POST['email'] : null;
        $this->password = array_key_exists("password", $_POST) ? $_POST['password'] : null;
        $this->isAdmin = array_key_exists("isAdmin", $_POST) ? $_POST['isAdmin'] : false;
        $this->randomizePassword = array_key_exists("randomizePassword", $_POST) ? $_POST['randomizePassword'] : false;
        $this->sendEmail = array_key_exists("sendEmail", $_POST) ? $_POST['sendEmail'] : false;
        
        // Ensure that isAdmin, randomizePassword and sendEmail are true or false:
        ( strtolower($this->isAdmin) === "true" ) ? $this->isAdmin = true : $this->isAdmin = false;
    	( strtolower($this->randomizePassword) === "true" ) ? $this->randomizePassword = true : $this->randomizePassword = false;
    	( strtolower($this->sendEmail) === "true" ) ? $this->sendEmail = true : $this->sendEmail = false;
    }

}