<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 11.12.13
 * Time: 12:15
 */

class UpdateAliasFilterPost extends AbstractPostAjaxRequestFilter {

    private $aliasId = null;
    private $email = null;
    private $username = null;


    /**
     * Private __constructor due to singleton pattern
     */
    public function __construct() {
        $this->parseRequest();
    }

    public function getAliasId()
    {
        return $this->aliasId;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getUsername()
    {
        return $this->username;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->aliasId) && !empty($this->email) && !empty($this->username)) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $json = parent::getJSON();

        $this->aliasId = array_key_exists('alias_id', $json) ? $json['alias_id'] : null;
        $this->email = array_key_exists('email', $json) ? $json['email'] : null;
        $this->username = array_key_exists('username', $json) ? $json['username'] : null;
    }

}