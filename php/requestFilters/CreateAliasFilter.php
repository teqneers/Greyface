<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class CreateAliasFilter {

    private $username = null;
    private $alias = null;

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
     * @return CreateAliasFilter|Singleton
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
    public function getAlias()
    {
        return $this->alias;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->username) && !empty($this->alias)) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->username = array_key_exists('username', $_GET) ? $_GET['username'] : null ;
        $this->alias = array_key_exists('alias', $_GET) ? $_GET['alias'] : null ;
    }

}