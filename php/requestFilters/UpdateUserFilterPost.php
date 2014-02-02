<?php

/*
 * This class tries to get the POST parameters of an update request.
 */
class UpdateUserFilterPost extends AbstractPostAjaxRequestFilter{

    private $userId = null;
    private $username = null;
    private $email = null;
    private $isAdmin = null;

    public function __construct(){
        $this->parseRequest();
    }

    public function getId()
    {
        return $this->userId;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function isComplete() {
        if ( !empty($this->userId) && !empty($this->username) && !empty($this->email) && isset($this->isAdmin) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $json = parent::getJSON();

        $this->userId = array_key_exists('user_id', $json) ? $json['user_id'] : null;
        $this->username = array_key_exists('username', $json) ? $json['username'] : null;
        $this->email = array_key_exists('email', $json) ? $json['email'] : null;

        $this->isAdmin = array_key_exists('is_admin', $json) ? $json['is_admin'] : null;
        $this->isAdmin = ($this->isAdmin >= 1) ? 1 : 0;
    }

}