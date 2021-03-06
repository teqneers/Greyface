<?php

/*
 * This class tries to get the POST parameters of an update request.
 */
class UpdateDomainFilterPost extends AbstractPostAjaxRequestFilter {

    private $newDomain = null;
    private $oldDomain = null;


    public function __construct() {
        $this->parseRequest();
    }

    public function getNewDomain()
    {
        return $this->newDomain;
    }
    public function getOldDomain()
    {
        return $this->oldDomain;
    }

    public function isComplete() {
        if ( !empty($this->oldDomain) && !empty($this->newDomain) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $json = parent::getJSON();

        if( array_key_exists("domain", $json) ) {
            $emailValues = parent::explodeOldNewValue($json["domain"]);
            $this->oldDomain = $emailValues["old"];
            $this->newDomain = $emailValues["new"];
        }
    }

}