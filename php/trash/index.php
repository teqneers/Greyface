<?php
//require "ApplicationException.php";
//require "DBQueryException.php";
//require "Logging.php";
require "DataBase.php";
require "Login.php";
require "User.php";

echo $_POST["a"];

$login = Login::getInstance();
$bool = $login->login();

echo $bool?"success":"nein";
