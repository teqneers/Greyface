<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use App\Doctrine\Migration\UserAliasesMigrationHelper;
use App\Doctrine\Migration\UsersMigrationHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version20220727125614 extends AbstractMigration implements ContainerAwareInterface
{
    use  UsersMigrationHelper, UserAliasesMigrationHelper;

    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'Copy old data to new tables';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            !$schema->hasTable('tq_user') &&
            !$schema->hasTable('tq_alias'),
            'Skipping because you might forgot to import the old data!'
        );

        $passHasher = $this->container->get('app.migration.password_hasher');

        if ($schema->hasTable('tq_user')) {

            /* Delete data from tables to prevent the duplicate data */
            $this->addSql('DELETE from tq_users');
            $this->addSql('DELETE from tq_aliases');
            /* end Delete data from tables */

            $selectUsers = $this->connection->executeQuery('SELECT * from tq_user');
            while ($row = $selectUsers->fetchAssociative()) {
                $userId    = (string)Uuid::uuid4();
                $row['id'] = $userId;
                $row['password'] = "admin";
                $this->addUser($row, $passHasher);

                /* now user conditions */
                if ($schema->hasTable('tq_alias')) {
                    $select = $this->connection->executeQuery(
                        'SELECT * from tq_alias where user_id = ' . $row['user_id']
                    );
                    $data   = [];
                    while ($row_u = $select->fetchAssociative()) {
                        $row_u['user_id'] = $userId;
                        $data[]           = $row_u;
                    }
                    $this->addUserAliases($data);
                }
                /* end user conditions */
            }
            echo 'done users and related data\n';
        }

        /* delete old tables */
        $this->addSql('DROP table if exists tq_alias cascade;');
        $this->addSql('DROP table if exists tq_user cascade;');
    }


    public
    function down(
        Schema $schema
    ): void {

    }

    public
    function isTransactional(): bool
    {
        return false;
    }
}
