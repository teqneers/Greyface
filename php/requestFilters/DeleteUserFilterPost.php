<?php

class DeleteUserFilterPost extends AbstractPostAjaxRequestFilter {

    private $username = null;

    public function __construct() {
        $this->parseRequest();
    }

    public function getUsername()
    {
        return $this->username;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->username) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->username = array_key_exists('username', $_POST) ? $_POST['username'] : null ;
    }

}