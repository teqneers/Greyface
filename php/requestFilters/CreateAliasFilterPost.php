<?php
/*
 * This class tries to get the POST parameters of a create alias request.
 */
class CreateAliasFilterPost extends AbstractPostAjaxRequestFilter {

    private $username = null;
    private $alias = null;

    /**
     * Private __constructor due to singleton pattern
     */
    public function __construct() {
        $this->parseRequest();
    }

    public function getUsername()
    {
        return $this->username;
    }
    public function getAlias()
    {
        return $this->alias;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->username) && !empty($this->alias)) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->username = array_key_exists('username', $_POST) ? $_POST['username'] : null ;
        $this->alias = array_key_exists('alias', $_POST) ? $_POST['alias'] : null ;
    }

}