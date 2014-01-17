<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class GreyfaceEntryFilter {

    private $senderName = null;
    private $domainName = null;
    private $src = null;
    private $rcpt = null;


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
     * @return GreyfaceEntryFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function getSenderName()
    {
        return $this->senderName;
    }
    public function getDomainName()
    {
        return $this->domainName;
    }
    public function getSource()
    {
        return $this->src;
    }
    public function getRecipient()
    {
        return $this->rcpt;
    }

    public function isComplete() {
        if ( isset($this->senderName) && isset($this->domainName) && isset($this->src) && isset($this->rcpt) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->senderName	= array_key_exists('senderName', $_GET) ? $_GET['senderName'] : null ;
        $this->domainName	= array_key_exists('domainName', $_GET) ? $_GET['domainName'] : null ;
        $this->src	= array_key_exists('src', $_GET) ? $_GET['src'] : null ;
        $this->rcpt	= array_key_exists('rcpt', $_GET) ? $_GET['rcpt'] : null ;
    }

}