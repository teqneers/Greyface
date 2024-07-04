<?php

use App\Kernel;

$script  = $_SERVER['SCRIPT_FILENAME'];
$appPath = dirname($script, 3);

$_SERVER['APP_PATH']            = $appPath;
$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'project_dir' => $appPath,
];

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
