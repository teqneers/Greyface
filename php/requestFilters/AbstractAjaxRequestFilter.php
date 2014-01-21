<?php

abstract class AbstractAjaxRequestFilter {

    private static $raw;
    private static $json;

    /**
     * @var Singleton instances
     */
    private static $instances = array();

    /**
     * Private __clone due to singleton pattern
     */
    private function __clone(){
        // Empty due to singleton pattern.
    }

    /**
     * The abstract inherited getInstance method of Singleton objects.
     *
     * @return AbstractAjaxRequestFilter
     */
    final public static function getInstance()
    {
        if(empty(self::$db)) {
            self::$raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            self::$json = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        }

        $class = get_called_class();                        // Gets the classname
        if(empty(self::$instances[$class])) {               // Look if there is already an instance of this class
            $rc = new ReflectionClass($class);              // ReflectionClass represents the Class which is submitted in the $class variable
            self::$instances[$class] = $rc->newInstance();  // Calls the constructor of the ReflectionClass of type $class. A new instance is born.
        }
        return self::$instances[$class];                // Returns the requested instance.
    }

    public function getJSON() {
        return self::$json;
    }
    public function getRAW() {
        return self::$raw;
    }
} 