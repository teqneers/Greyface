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
     * @return Config|Singleton
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

    /**
     * Reads the ini file
     */
    private function readIni() {
        $pathToIni = realpath("../../greyface.ini");
        $ini_array = parse_ini_file($pathToIni, TRUE);
        $this->db_hostname = $ini_array["mySQLData"]["hostname"];
        $this->db_username = $ini_array["mySQLData"]["username"];
        $this->db_password = $ini_array["mySQLData"]["password"];
        $this->db_name   = $ini_array["mySQLData"]["dbName"];

        $this->app_sendMail   = ($ini_array["application"]["sendMail"] == true) ? true : false;
        $this->app_logging   = ($ini_array["application"]["logging"] == true) ? true : false;
    }

    /*
     * Gets the database hostname which is specified in the ini file
          *
     * @return string - db_hostname
     */
    public function getHostname() {
        return $this->db_hostname;
    }

    /*
     * Gets the database passwort which is specified in the ini file
     *
     * @return string - db_password
     */
    public function getDbPassword()
    {
        return $this->db_password;
    }

    /*
     * Gets the database username which is specified in the ini file
     *
     * @return string - db_username
     */
    public function getDbUsername()
    {
        return $this->db_username;
    }

    /*
     * Gets the database name which is specified in the ini file
     *
     * @return string - db_password
     */
    public function getDbName()
    {
        return $this->db_name;
    }

    /*
     * Gets the sendmail option which is specified in the ini file
     * This specifies if mails can be send by greyface to newly created users.
     *
     * @return string - app_sendMail
     */
    public function isSendMail()
    {
        return $this->app_sendMail;
    }

    /*
     * Gets the logging option which is specified in the ini file
     * This specifies if logging will be activated or not
     *
     * @return string - app_sendMail
     */
    public function isLogging()
    {
        return $this->app_logging;
    }
}