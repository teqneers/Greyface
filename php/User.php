<?php
/**
 * Created by PhpStorm.
 * User: svencc
 * Date: 09.12.13
 * Time: 11:16
 */

class User {

    private $isUserExisting = false;
    private $user_id = null;
    private $username = null;
    private $email = null;
    private $password = null;
    private $is_admin = null;
    private $session= null;
    private $cookie= null;
    private $ip= null;


    /**
     * Creates a user with the given username
     * @param String $username
     * @param bool $searchUser - set this flag to false if you do not want to load a user from the db. Only creates an empty User object.
     */
    private function __construct($username, $searchUser = true) {
        if($searchUser) {
            $userResult = $db = DataBase::getInstance()->queryArray("SELECT * FROM tq_user WHERE username='".DataBase::getInstance()->quote($username)."'");
            if(isset($userResult[0])) {
                $this->isUserExisting = true;
                $this->user_id = $userResult[0]["user_id"];
                $this->username = $userResult[0]["username"];
                $this->email = $userResult[0]["email"];
                $this->password = $userResult[0]["password"];
                $this->is_admin = (boolean)$userResult[0]["is_admin"];
                $this->session = $userResult[0]["session"];
                $this->cookie = $userResult[0]["cookie"];
                $this->ip = $userResult[0]["ip"];
            }
        }
    }

    /**
     * Creates a user with the given username
     * @param String $username
     * @return User object
     */
    public static function getUserByName($username) {
        return new User($username);
    }

    /**
     * @param String $userId - The userId you of the searched user
     * @return User object
     */
    public static function getUserById($userId) {
        $userResult = DataBase::getInstance()->queryArray("SELECT * FROM tq_user WHERE user_id='".DataBase::getInstance()->quote($userId)."'");
        if(isset($userResult[0])) {
            return new User($userResult[0]["username"]);
        } else {
            return new User("", false);
        }
    }

    /**
     * @return bool if user exists or not
     */
    public function isUserExisting() {
        return $this->isUserExisting;
    }

    /**
     * Returns if user has admin rights or not.
     * This method is a shortcut for getIsAdmin()
     * @return mixed
     */
    public function isAdmin() {
        return $this->getIsAdmin();
    }

    /**
     * @return String cookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @return String email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return String ip
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    /**
     * @return String password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return String session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return String user_id
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return String username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * saves the session to database.
     * @param String $session
     */
    public function setSession($session) {
        $affectedRows = DataBase::getInstance()->queryAffect("UPDATE tq_user SET session = '".$session."' WHERE user_id = '".$this->user_id."'");
    }

    /**
     * Creates and saves the cookie in the database.
     * @return String cookieHash
     */
    public function createAndSaveCookie() {
        $info = array(
            $this->username, $this->email, $this->getPassword()
        );

        return $this->setCookie(md5(serialize($info)));
    }

    /**
     * Writes cookie to database and changes the local cookie variable.
     * @param $cookieHash
     * @return String $cookieHash
     */
    private function setCookie($cookieHash) {
        // save serialized cookie array to db
        $res = DataBase::getInstance()->queryAffect(
            "UPDATE tq_user"
            ." SET cookie = '".$cookieHash."'"
            ." WHERE user_id = '".$this->user_id."'"
        );
        // update local cookie variable
        return $this->cookie = $cookieHash;
    }

    public function setPassword($password) {
        if(empty($password)) {
            return false;
        } else {
            $res = DataBase::getInstance()->queryAffect(
                "UPDATE tq_user"
                ." SET password = '".$this->encryptPassword($password)."'"
                ." WHERE user_id = '".$this->user_id."'"
            );
            return true;
        }
    }

    public function unsetCookie() {
        $this->setCookie("");
        return true;
    }

    public function hasCookieInDB() {
        if (empty($this->cookie)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Generates random password
     *
     * @param int - $length - The length of the requested password string.
     * @return string - The password string.
     */
    public static function generateRandomizedPassword($length = 8) {

        $length = (int) $length;

        $chars = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,e,s,t,u,v,w,x,y,z,1,2,3,4,5,6,7,8,9,0,.,!,?,$";
        $array=explode(",",$chars);
        shuffle($array);
        $newPwString = implode($array,"");
        return substr($newPwString, 0, $length);
    }

    public static function encryptPassword($passwordToEncrypt) {
        return sha1($passwordToEncrypt);
    }

}