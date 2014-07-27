<?php
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);

    // Error handling
    require "../../php/errorHandler/Handler.php";



    require "../../php/database/DataBase.php";
    require "../../php/database/DBException.php";
    require "../../php/ajaxResult/AjaxResult.php";
    require "../../php/ajaxResult/AjaxRowResult.php";
    require "../../php/Login.php";
    require "../../php/LoginResult.php";
    require "../../php/User.php";

    header("Content-Type: application/json");
    $login = Login::getInstance();
    $loginResult = $login->login();

    if ($loginResult->getResult()) {
        $userStatusString = ($loginResult->getUser()->isAdmin()) ? "true" : "false";
        echo '{"success": ' . $loginResult->getResultString() . ', "msg": "' . $loginResult->getMsg() . '", isAdmin: ' . $userStatusString . ', usr:{username: "'.$loginResult->getUser()->getUsername().'", email: "'.$loginResult->getUser()->getEmail().'", userId: "'.$loginResult->getUser()->getUserId().'", isAdmin:'.$userStatusString.' }}';
    } else {
        echo '{"success": ' . $loginResult->getResultString() . ', "msg": "' . $loginResult->getMsg() . '"}';
    }

?>