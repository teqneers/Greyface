<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class DeleteAliasFilter {

    private $aliasId = null;

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
     * @return DeleteAliasFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }


    public function getAliasId()
    {
        return $this->aliasId;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->aliasId) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->aliasId = array_key_exists('alias_id', $_GET) ? $_GET['alias_id'] : null ;
    }

}