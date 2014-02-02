<?php

/*
 * This class tries to get the GET parameters of read/filter request.
 */
class ReadRequestFilter {

    private $limit = null;
    private $start = null;

    private $sortProperty = null;
    private $sortDirection = null;

    private $filterStore = array();

    /**
     * @var Singleton instance
     */
    private static $instance = null;

    /**
     * Private __constructor due to singleton pattern
     */
    private function __construct() {
        $this->parseRequest();
    }

    /**
     * Private __clone due to singleton pattern
     */
    private function __clone(){
        // Empty due to singleton pattern.
    }

    /**
     * Singleton getInstance method.
     * @return ReadRequestFilter|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }



    public function hasPagingItems() {
        if (isset($this->limit, $this->start)) {
            return true;
        }
        return false;
    }

    public function hasSortingItems() {
        if(isset($this->sortProperty, $this->sortDirection)) {
            return true;
        }
        return false;
    }

    public function hasFilteringItems() {
        if(count($this->filterStore) > 0) {
            return true;
        }
        return false;
    }



    public function getLimit()
    {
        return $this->limit;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getSortProperty()
    {
        return $this->sortProperty;
    }

    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    /**
     * Gets the requested Filters.
     * One Filter has this structure: array["property" => "filter"]
     *
     * @return array[
     *              "property" => "value"],
     *              "property" => "value"],
     *              ...
     *              "property" => "filter"]
     *         ]
     */
    public function getFilters() {
        return $this->filterStore;
    }



    public function parseRequest() {
        $this->parseForPaging();
        $this->parseForSorting();
        $this->parseForFiltering();
    }

    private function parseForPaging() {
        $this->limit	= array_key_exists('limit', $_GET) ? $_GET['limit'] : 100 ;
        $this->start	= array_key_exists("start", $_GET) ? $_GET['start'] : 0;
    }

    private function parseForSorting() {
        $this->sortProperty	= array_key_exists("sort", $_GET) ? json_decode($_GET['sort'],true)[0]["property"] : NULL;
        $sortDirection	= array_key_exists("sort", $_GET) ? json_decode($_GET['sort'],true)[0]["direction"] : NULL;
        $this->sortDirection = (strtoupper($sortDirection)=="ASC") ? "ASC" : "DESC"; // To ensure that sortDescription is "ASC" or "DESC"
    }

    private function parseForFiltering() {
        // Check if "filter"-key exists in $_GET[] and decodes the json-value
        $filters = array_key_exists("filter", $_GET) ? json_decode($_GET['filter'],true) : NULL;
        // If $filters was set, go on and check each item of the array.
        if (isset($filters)) {
            foreach($filters as $filter) {
                // If "property" and "value" keys exist, go on.
                if(
                    array_key_exists("property", $filter ) &&
                    array_key_exists("value", $filter )
                ) {
                    // check if property is not an empty string
                    if($filter["property"] != "") {
                        // add filter property=>value to the $filterStore[]
                        $this->filterStore[$filter["property"]] = $filter["value"];
                    }
                }
            }
        }
    }
}