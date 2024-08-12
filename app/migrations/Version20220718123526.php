<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220718123526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates initial user and authentication tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE tq_users (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', username VARCHAR(128) NOT NULL, email VARCHAR(128) NOT NULL, role VARCHAR(16) NOT NULL, password VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(128) DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_by VARCHAR(128) DEFAULT NULL, UNIQUE INDEX uniq_username (username, deleted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastUsed DATETIME NOT NULL, class VARCHAR(100) NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY(series)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE tq_aliases (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', alias_name VARCHAR(128) NOT NULL, INDEX IDX_B5881F2FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE tq_aliases ADD CONSTRAINT FK_B5881F2FA76ED395 FOREIGN KEY (user_id) REFERENCES tq_users (id) ON DELETE CASCADE'
        );

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tq_users');
        $this->addSql('DROP TABLE rememberme_token');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE tq_aliases');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
