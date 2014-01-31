<?php

class GreyfaceEntryFilterPost extends AbstractPostAjaxRequestFilter {

    private $senderName = null;
    private $domainName = null;
    private $src = null;
    private $rcpt = null;


    public function __construct() {
        $this->parseRequest();
    }

    public function getSenderName()
    {
        return $this->senderName;
    }
    public function getDomainName()
    {
        return $this->domainName;
    }
    public function getSource()
    {
        return $this->src;
    }
    public function getRecipient()
    {
        return $this->rcpt;
    }

    public function isComplete() {
        if ( isset($this->senderName) && isset($this->domainName) && isset($this->src) && isset($this->rcpt) ) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $this->senderName	= array_key_exists('senderName', $_POST) ? $_POST['senderName'] : null ;
        $this->domainName	= array_key_exists('domainName', $_POST) ? $_POST['domainName'] : null ;
        $this->src	= array_key_exists('src', $_POST) ? $_POST['src'] : null ;
        $this->rcpt	= array_key_exists('rcpt', $_POST) ? $_POST['rcpt'] : null ;
    }

}