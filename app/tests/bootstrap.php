<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
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

/**
 * Rebuilds the test database from scratch.
 *
 * This drops and recreates the database in DATABASE_URL, so it must only ever
 * point at a throw-away database. Set DISABLE_DB_SETUP=1 to skip it when the
 * schema is already current and you want a faster run.
 */
function bootstrapDatabase(): void
{
    $kernel = new Kernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $commands = [
        ['command' => 'doctrine:database:drop', '--if-exists' => '1', '--force' => '1'],
        ['command' => 'doctrine:database:create'],
        ['command' => 'doctrine:migrations:migrate', '--allow-no-migration' => '1', '--no-interaction' => '1'],
    ];

    foreach ($commands as $command) {
        // Buffer the output so a successful run stays quiet, but the real error
        // is available when something goes wrong.
        $output = new BufferedOutput();
        $exitCode = $application->run(new ArrayInput($command), $output);

        if ($exitCode !== 0) {
            $kernel->shutdown();

            throw new RuntimeException(sprintf(
                "Test database setup failed.\n\n"
                . "  command   %s\n"
                . "  exit code %d\n"
                . "  database  %s\n\n"
                . "%s\n"
                . "Start the database with `docker compose up -d database` from the project root.\n"
                . "To point at a different server, create a .env.test.local next to .env with your\n"
                . "own DATABASE_URL — note that .env.local is deliberately ignored when APP_ENV=test,\n"
                . "so .env.test.local is the only file that works for tests.",
                $command['command'],
                $exitCode,
                preg_replace('#://[^@/]*@#', '://***@', $_ENV['DATABASE_URL'] ?? '(unset)'),
                trim($output->fetch())
            ));
        }
    }

    $kernel->shutdown();
}

if (($_SERVER['APP_ENV'] ?? '') === 'test' && ($_SERVER['DISABLE_DB_SETUP'] ?? '0') === '0') {
    bootstrapDatabase();
}
