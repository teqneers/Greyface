<?php

/*
 * This class tries to get the POST parameters of an update request.
 */
class UpdateEmailFilterPost extends AbstractPostAjaxRequestFilter {

    private $newEmail = null;
    private $oldEmail = null;


    /**
     * Private __constructor due to singleton pattern
     */
    public function __construct() {
        $this->parseRequest();
    }

    public function getNewEmail()
    {
        return $this->newEmail;
    }
    public function getOldEmail()
    {
        return $this->oldEmail;
    }

    public function isComplete() {
        if ( !empty($this->oldEmail) && !empty($this->newEmail) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $json = parent::getJSON();

        if( array_key_exists("email", $json) ) {
            $emailValues = parent::explodeOldNewValue($json["email"]);
            $this->oldEmail = $emailValues["old"];
            $this->newEmail = $emailValues["new"];
        }
    }

}