<?php
    require "../../php/database/DataBase.php";
    require "../../php/Login.php";
    require "../../php/LoginResult.php";
    require "../../php/User.php";

    header("Content-Type: application/json");

    // Login
    $login = Login::getInstance();
    $loginResult = $login->login();

    if($loginResult->getResult()) {
        $loginResult->getUser()->setSession("");    // Delete session hash in database
        $logoutResult = $login->logout();           // Destroy
        echo '{"success": ' . $logoutResult->getResultString() . ', "msg": "' . $logoutResult->getMsg() . '"}';
    } else {
        echo '{"success": false, "msg": "User is not logged it"}';
    }
?>