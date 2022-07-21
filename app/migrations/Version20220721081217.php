<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220721081217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds sqlgrey tables if not exist, this will be the only case for Test DB.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE IF NOT EXISTS config (parameter VARCHAR(128) NOT NULL, value VARCHAR(128) DEFAULT NULL, PRIMARY KEY(parameter)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE IF NOT EXISTS connect (sender_name VARCHAR(64) NOT NULL,sender_domain VARCHAR(255) NOT NULL,src VARCHAR(39) NOT NULL,rcpt VARCHAR(255) NOT NULL,first_seen timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),KEY connect_idx (src,sender_domain,sender_name), KEY connect_fseen (first_seen)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS domain_awl (sender_domain VARCHAR(255) NOT NULL,src VARCHAR(39) NOT NULL,first_seen timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),last_seen timestamp NOT NULL DEFAULT "0000-00-00 00:00:00",PRIMARY KEY (src,sender_domain),KEY domain_awl_lseen (last_seen)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS from_awl ( sender_name VARCHAR(64) NOT NULL, sender_domain VARCHAR(255) NOT NULL, src VARCHAR(39) NOT NULL, first_seen timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),last_seen timestamp NOT NULL DEFAULT "0000-00-00 00:00:00",PRIMARY KEY (src,sender_domain,sender_name),KEY from_awl_lseen (last_seen)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS optin_domain (domain VARCHAR(255) NOT NULL,PRIMARY KEY (domain)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE IF NOT EXISTS optin_email (email VARCHAR(255) NOT NULL,PRIMARY KEY (email)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS optout_domain (domain VARCHAR(255) NOT NULL,PRIMARY KEY (domain))  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS optout_email (email VARCHAR(255) NOT NULL, PRIMARY KEY (email))  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE connect');
        $this->addSql('DROP TABLE domain_awl');
        $this->addSql('DROP TABLE from_awl');
        $this->addSql('DROP TABLE optin_domain');
        $this->addSql('DROP TABLE optin_email');
        $this->addSql('DROP TABLE optout_domain');
        $this->addSql('DROP TABLE optout_email');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
