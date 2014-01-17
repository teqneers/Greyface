<?php
/**
 * /**
 * Created by PhpStorm.
 * User: svencc
 * Date: 06.12.13
 * Time: 16:00
 *
 * This class is responsible for login/auto login the user.
 * This includes authentication and authorization.
 * The login-method returns an object of type LoginResult,
 * which gives you an instance of the logged in USER!
 */
class Login {

    /**
     * @var Login Singleton instance
     */
    private static $instance = null;

    /**
     * @var DataBase|null The dataBase connection instance.
     */
    private $dataBase = null;

    /**
     * @var Instance of login result
     */
    private $loginResult = null;

    /**
     * Successfully logged-in user instance
     *
     * @var User|null
     */
    private $loggedInUser = null;



    /**
     * Private __constructor due to singleton pattern
     */
    private function __construct(){
        $this->dataBase = DataBase::getInstance();
    }

    /**
     * Private __clone due to singleton pattern
     */
    private function __clone(){
        // Empty due to singleton pattern.
    }

    /**
     * Singleton getInstance method
     *
     * @return Login|Singleton
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Trys to authenticate the user
     *
     * @return LoginResult - instance of LoginResult with the result of login attempt
     */
    public function login(){
        session_start();

        // Check if credentials are in $_POST[] array (first login)
        // or already in $SESSION[] array (already logged in.

        // user uses login form
        if ( isset($_POST["username"]) && isset($_POST["password"]) ){
            $user = User::getUserByName($_POST["username"]);
            if( $user->isUserExisting() ) {                                                                             // Does user exist in db?
                if( $user->getPassword() == User::encryptPassword($_POST["password"]) ) {                                                   // Is valid password?
                    $sessionString = serialize(array("username" => $_POST["username"], "session" => session_id()));     // Create session String
                    $_SESSION["greyFaceLogin"] = $sessionString;                                                        // Write to SESSION[]
                    $user->setSession($sessionString);                                                                  // Write session id to database

                    $this->setLoggedInUser($user);
                    // checks if remember me cookie should be saved
                    if( isset($_POST["rememberLogin"]) && $_POST["rememberLogin"] == "on") {
                        setcookie("greyFaceRememberMe", $user->createAndSaveCookie(), time()+31557600);                 // creates a cookie which expires in 1 year
                        setcookie("userId", $user->getUserId(), time()+31557600);                                       // creates a cookie which expires in 1 year
                    }
                    return $this->setLoginResult(new LoginResult(true, "User is logged in.", $user));
                }
                return $this->setLoginResult(new LoginResult(false, "Invalid user/password.", null));
            }
            return $this->setLoginResult(new LoginResult(false, "Invalid user/password.", null));
        }

        // user is already in session
        if(isset($_SESSION["greyFaceLogin"])) {
            $session = unserialize( $_SESSION["greyFaceLogin"] );           // Get the user from the session
            $user = User::getUserByName($session["username"]);              // Check if the user exists?
            if($user->isUserExisting()) {                                   // Check if user exists
                $user->getSession() == $_SESSION["greyFaceLogin"];          // Check if SessionHash() is the same as in the database?

                return $this->setLoginResult(new LoginResult(true, "User is authenticated due the session", $user));
            }
            return $this->setLoginResult(new LoginResult(false, "The user which is stored in the session is not valid.", null));
        }

        // check if the user has a valid cookie?
        if(isset($_COOKIE["greyFaceRememberMe"]) && isset($_COOKIE["userId"])) {
            $user = User::getUserById($_COOKIE["userId"]);
            if ($user->isUserExisting()) {
                if($user->hasCookieInDB()) {
                    if($user->getCookie() == $_COOKIE["greyFaceRememberMe"]) {

                        return $this->setLoginResult(new LoginResult(true, "User recognized with cookie", $user));
                    }
                }
                return $this->setLoginResult(new LoginResult(false, "User has an unset Cookie in DB - he logged out", null));
            } else {
                return $this->setLoginResult(new LoginResult(false, "User has an invalid cookie. Deserialzation failed", null));
            }
        }

        return $this->setLoginResult(new LoginResult(false, "User cannot be identified.", null));
    }

    /**
     * Logout
     *
     * Destroys the session and clears the $_SESSION array.
     * @return LoginResult
     */
    public function logout(){
        $_SESSION[] = array();
        $user = $this->loginResult->getUser();
        $user->unsetCookie();
        setcookie("greyFaceRememberMe", "", 0);
        setcookie("userId", "", 0);
        session_destroy();
        return $this->setLoginResult(new LoginResult(true, "User logged out", null ));
    }

    /**
     * Sets the LoginResult
     *
     * @return LoginResult
     */
    public function getLoginResult()
    {
        return $this->loginResult;
    }

    /**
     * Gets the successfully logged-in user instance
     *
     * @return User|null
     */
    public function getLoggedInUser() {
        return $this->loggedInUser;
    }

    /**
     * Sets the LoginResult and RETURNS the assigned LoginResult BACK!
     * @param LoginResult instance with status of login
     * @return LoginResult
     */
    private function setLoginResult($loginResult) {
        $this->loginResult = $loginResult;
        return $loginResult;
    }

    /**
     * Sets the successfully logged-in user instance
     *
     * @param $user
     */
    private function setLoggedInUser($user) {
        $this->loggedInUser = $user;
    }

}