<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use App\Doctrine\Migration\UsersMigrationHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718125037 extends AbstractMigration implements ContainerAwareInterface
{
    use UsersMigrationHelper;

    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'Add first Admin user with name and password "admin" ';
    }

    public function up(Schema $schema): void
    {
        $passHasher = $this->container->get('app.migration.password_hasher');

        $user = [
            'id' => (string)Uuid::uuid4(),
            'username' => 'admin',
            'email' => 'root@localhost',
            'password' => 'admin',
            'is_admin' => 1
        ];
        $this->addUser($user, $passHasher);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE from tq_users where username="admin"');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
