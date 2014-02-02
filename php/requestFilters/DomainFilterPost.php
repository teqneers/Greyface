<?php

/*
 * This class tries to get the POST parameters from a request to create and delete Domains in the Whitelist/Blacklist.
 */
class DomainFilterPost extends AbstractPostAjaxRequestFilter {

    private $domain = null;

    public function __construct() {
        $this->parseRequest();
    }

    public function getDomain()
    {
        return $this->domain;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->domain) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->domain = array_key_exists('domain', $_POST) ? $_POST['domain'] : null ;
    }

}