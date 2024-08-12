<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use App\Doctrine\Migration\UsersMigrationHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

final class Version20220718125037 extends AbstractMigration
{
    use UsersMigrationHelper;

    public function getDescription(): string
    {
        return 'Add first Admin user with name and password "admin" ';
    }

    public function up(Schema $schema): void
    {

        $user = [
            'id' => (string)Uuid::uuid4(),
            'username' => 'admin',
            'email' => 'root@localhost',
            'is_admin' => 1
        ];
        $this->addUser($user);
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
