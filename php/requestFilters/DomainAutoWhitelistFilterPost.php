<?php

class DomainAutoWhitelistFilterPost extends AbstractPostAjaxRequestFilter {

    private $domain = null;
    private $source = null;

    public function __construct() {
        $this->parseRequest();
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
    public function isComplete() {
        if ( !empty($this->domain) && !empty($this->source) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->domain = array_key_exists("domain", $_POST) ? $_POST['domain'] : null;
        $this->source = array_key_exists("source", $_POST) ? $_POST['source'] : null;
    }

}