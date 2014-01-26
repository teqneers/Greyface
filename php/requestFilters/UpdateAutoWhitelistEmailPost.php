<?php

class UpdateAutoWhitelistEmailPost extends AbstractPostAjaxRequestFilter {

    private $sender_domain_id = null;
    private $sender_name_id = null;
    private $src_id = null;

    private $sender_domain = null;
    private $sender_name = null;
    private $src = null;


    /**
     * Private __constructor due to singleton pattern
     */
    public function __construct() {
        $this->parseRequest();
    }

    public function getSenderDomainId()
    {
        return $this->sender_domain_id;
    }
    public function getSenderNameId()
    {
        return $this->sender_name_id;
    }
    public function getSrcId()
    {
        return $this->src_id;
    }

    public function getSenderDomain()
    {
        return $this->sender_domain;
    }

    public function getSenderName()
    {
        return $this->sender_name;
    }

    public function getSrc()
    {
        return $this->src;
    }




    // Checks if email is set.
    public function isComplete() {
        if ( !empty($this->sender_domain) && !empty($this->sender_name) && !empty($this->src) && !empty($this->sender_domain_id) && !empty($this->sender_name_id) && !empty($this->src_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function parseRequest() {
        $json = parent::getJSON();

        if (array_key_exists('dynamicId', $json)) {
            $this->sender_domain_id = array_key_exists('sender_domain', $json['dynamicId']) ? $json['dynamicId']['sender_domain'] : null;
            $this->sender_name_id = array_key_exists('sender_name', $json['dynamicId']) ? $json['dynamicId']['sender_name'] : null;
            $this->src_id = array_key_exists('src', $json['dynamicId']) ? $json['dynamicId']['src'] : null;
        }

        $this->sender_domain = array_key_exists('sender_domain', $json) ? $json['sender_domain'] : null;
        $this->sender_name = array_key_exists('sender_name', $json) ? $json['sender_name'] : null;
        $this->src = array_key_exists('src', $json) ? $json['src'] : null;
    }

}