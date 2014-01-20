<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class CreateUserFilter {

    private $username = null;
    private $email = null;
    private $password = null;
    private $isAdmin = null;
    private $randomizePassword = null;
    private $sendEmail = null;

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
     * @return CreateUserFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
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
        $this->username = array_key_exists('username', $_GET) ? $_GET['username'] : null ;
        $this->email = array_key_exists("email", $_GET) ? $_GET['email'] : null;
        $this->password = array_key_exists("password", $_GET) ? $_GET['password'] : null;
        $this->isAdmin = array_key_exists("isAdmin", $_GET) ? $_GET['isAdmin'] : false;
        $this->randomizePassword = array_key_exists("randomizePassword", $_GET) ? $_GET['randomizePassword'] : false;
        $this->sendEmail = array_key_exists("sendEmail", $_GET) ? $_GET['sendEmail'] : false;
        
        // Ensure that isAdmin, randomizePassword and sendEmail are true or false:
        ( strtolower($this->isAdmin) === "true" ) ? $this->isAdmin = true : $this->isAdmin = false;
    	( strtolower($this->randomizePassword) === "true" ) ? $this->randomizePassword = true : $this->randomizePassword = false;
    	( strtolower($this->sendEmail) === "true" ) ? $this->sendEmail = true : $this->sendEmail = false;
    }

}