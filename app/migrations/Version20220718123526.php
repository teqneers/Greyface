<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718123526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates intial user and authentication tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE tq_users (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', username VARCHAR(128) NOT NULL, email VARCHAR(128) NOT NULL, role VARCHAR(16) NOT NULL, password VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(128) DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_by VARCHAR(128) DEFAULT NULL, UNIQUE INDEX uniq_username (username, deleted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastUsed DATETIME NOT NULL, class VARCHAR(100) NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY(series)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tq_users');
        $this->addSql('DROP TABLE rememberme_token');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
