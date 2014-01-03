<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class EmailFilter {

    private $email = null;

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
     * @return EmailFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }


    public function getEmail()
    {
        return $this->email;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->email) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->email = array_key_exists('email', $_GET) ? $_GET['email'] : null ;
    }

}