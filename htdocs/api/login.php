<?php
    require "../../php/database/DataBase.php";
    require "../../php/Login.php";
    require "../../php/LoginResult.php";
    require "../../php/User.php";

    header("Content-Type: application/json");
    $login = Login::getInstance();
    $loginResult = $login->login();

    echo '{"success": ' . $loginResult->getResultString() . ', "msg": "' . $loginResult->getMsg() . '"}';
    ?>
