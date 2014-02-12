<?php

/**
 * Class Config
 * reads the configuration from the .ini file
 */
class Config {

    private $db_hostname;
    private $db_username;
    private $db_password;
    private $db_name;

    private $app_sendMail;
    private $app_logging;
    private $app_displayErrors;

    private $application_name = "Greyface";

    /**
     * @var Singleton instance
     */
    private static $instance = null;

    /**
     * Private __clone due to singleton pattern
     */
    private function __clone(){
        // Empty due to singleton pattern.
    }

    /**
     * Singleton getInstance method.
     * @return Config
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Private __constructor due to singleton pattern
     */
    private function __construct(){
        $this->readIni();
    }

    private function readIni() {
        $pathToIni = realpath("../../greyface.ini");
        $ini_array = parse_ini_file($pathToIni, TRUE);

        if( array_key_exists('mySQLData',  $ini_array) ) {
            $this->db_hostname       = array_key_exists('hostname', $ini_array["mySQLData"]) ? $ini_array["mySQLData"]["hostname"] : null;
            $this->db_username       = array_key_exists('username', $ini_array["mySQLData"]) ? $ini_array["mySQLData"]["username"] : null;
            $this->db_password       = array_key_exists('password', $ini_array["mySQLData"]) ? $ini_array["mySQLData"]["password"] : null;
            $this->db_name           = array_key_exists('dbName', $ini_array["mySQLData"]) ? $ini_array["mySQLData"]["dbName"] : null;
        }

        if( array_key_exists('application',  $ini_array) ) {
            $this->app_sendMail      = array_key_exists('sendMail', $ini_array["application"]) ? $ini_array["application"]["sendMail"] : null;
            $this->app_logging       = array_key_exists('logging',  $ini_array["application"]) ? $ini_array["application"]["logging"] : null;
            $this->app_displayErrors = array_key_exists('displayErrors',  $ini_array["application"]) ? $ini_array["application"]["displayErrors"] : null;
        }

        if ( !$this->isIniSet() ) {
            throw new Exception('Ini is not set properly!');
        }
    }

    private function isIniSet() {
        return ( empty($this->db_hostname)   ||
                 empty($this->db_username)   ||
                 empty($this->db_password)   ||
                 empty($this->db_name)       ||
                 empty($this->app_sendMail)  ||
                 empty($this->app_logging)   ||
                 empty($this->app_displayErrors)
        );
    }

    public function getHostname() {
        return $this->db_hostname;
    }

    public function getDbPassword()
    {
        return $this->db_password;
    }

    public function getDbUsername()
    {
        return $this->db_username;
    }

    public function getDbName()
    {
        return $this->db_name;
    }

    public function isSendMail()
    {
        return $this->app_sendMail;
    }

    public function isLogging()
    {
        return $this->app_logging;
    }

    public function isErrorOutput()
    {
        return $this->app_logging;
    }
}