<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists(Dotenv::class)) {
    throw new LogicException(
        'You need to add "symfony/dotenv" as Composer dependencies.'
    );
}

$appPath = dirname(__DIR__, 2);
$_SERVER['APP_PATH'] = $appPath;

(new Dotenv())->bootEnv($appPath . '/.env');

function bootstrapDatabase()
{
    $kernel = new Kernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);
    $application->run(
        new ArrayInput(
            [
                'command' => 'doctrine:database:drop',
                '--if-exists' => '1',
                '--force' => '1',
            ]
        )
    );
    $application->run(
        new ArrayInput(
            [
                'command' => 'doctrine:database:create',
            ]
        )
    );
    $application->run(
        new ArrayInput(
            [
                'command' => 'doctrine:migrations:migrate',
                '--allow-no-migration' => '1',
                '--no-interaction' => '1',
            ]
        )
    );
    $kernel->shutdown();
}

if (($_SERVER['APP_ENV'] ?? '') === 'test' && ($_SERVER['DISABLE_DB_SETUP'] ?? '0') === '0') {
    bootstrapDatabase();
}
