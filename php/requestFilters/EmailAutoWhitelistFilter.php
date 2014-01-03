<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class EmailAutoWhitelistFilter {

    private $sender = null;
    private $domain = null;
    private $source = null;

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
     * @return AddEmailAutoWhitelistFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }


    public function getSender()
    {
        return $this->sender;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getSource()
    {
        return $this->source;
    }

    // Checks and Validates the tupel.
    // Also checks if the given IP-Address is in valid ipv4/ipv6 format - if its not it returns false!
    public function isTupelComplete() {
        if ( !empty($this->sender) && !empty($this->domain) && !empty($this->source) ) {
            return true;
        } else {
            return false;
        }
    }

    public function isValidIp() {
        return @inet_pton($this->source) ? true : false;
    }

    public function parseRequest() {
        $this->sender = array_key_exists('sender', $_GET) ? $_GET['sender'] : null ;
        $this->domain = array_key_exists("domain", $_GET) ? $_GET['domain'] : null;
        $this->source = array_key_exists("source", $_GET) ? $_GET['source'] : null;
    }

}