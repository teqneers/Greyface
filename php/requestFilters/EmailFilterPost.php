<?php

/*
 * This class tries to get the POST parameters from a request to create and delete Emails in the Whitelist/Blacklist.
 */
class EmailFilterPost extends AbstractPostAjaxRequestFilter {

    private $email = null;

    public function __construct() {
        $this->parseRequest();
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
        $this->email = array_key_exists('email', $_POST) ? $_POST['email'] : null ;
    }

}