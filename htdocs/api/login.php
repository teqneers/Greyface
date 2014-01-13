<?php
    require "../../php/database/DataBase.php";
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