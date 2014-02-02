<?php

/*
 * This class tries to get the POST parameters of a delete alias request.
 */
class DeleteAliasFilterPost extends AbstractPostAjaxRequestFilter {

    private $aliasId = null;

    public function __construct() {
        $this->parseRequest();
    }

    public function getAliasId()
    {
        return $this->aliasId;
    }

    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->aliasId) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->aliasId = array_key_exists('alias_id', $_POST) ? $_POST['alias_id'] : null ;
    }

}