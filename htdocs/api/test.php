<?php
require "../../php/Config.php";


$config=Config::getInstance();
print $config->getHostname();
echo $config->getDbUsername();
echo $config->getDbPassword();
echo $config->getDbName();