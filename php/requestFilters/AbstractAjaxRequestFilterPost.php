<?php

abstract class AbstractPostAjaxRequestFilter {

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
     * @return AbstractPostAjaxRequestFilter
     */
    final public static function getInstance()
    {
        if( array_key_exists("HTTP_RAW_POST_DATA", $GLOBALS)  ) {
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

    /**
     * Gets the decoded json string from $GLOBALS['HTTP_RAW_POST_DATA']
     * @return array -  json decoded php array
     */
    public function getJSON() {
        return self::$json;
    }

    /**
     * Gets the raw json string from $GLOBALS['HTTP_RAW_POST_DATA']
     * @return string
     */
    public function getRAW() {
        return self::$raw;
    }

    /**
     * Splits a string, seperated with "--->" to get the old and new value.
     * @param string - $value - A string in the form "email@old.de--->email@new.de"
     * @return array - An associative array with the indexes "old" and "new". ["old"=>"emaol@old.de"] and ["new"=>"emaol@new.de"].
     */
    protected function explodeOldNewValue($value) {
        $oldNnew = explode("--->",$value);
        return array(
            "old" => $oldNnew[0],
            "new" => $oldNnew[1],
        );
    }
} 