<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class ChangeUserPasswordFilter {

    private $username = null;
    private $password = null;

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
     * @return ChangeUserPasswordFilter|Singleton
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

    public function getPassword()
    {
        return $this->password;
    }

    // Checks and Validates the tupel.
    public function isTupelComplete() {
        if ( !empty($this->username) && !empty($this->password) ) {
                return true;
        }
        return false;
    }

    public function parseRequest() {
        $this->username = array_key_exists('username', $_GET) ? $_GET['username'] : null ;
        $this->password = array_key_exists("password", $_GET) ? $_GET['password'] : null;
    }

}