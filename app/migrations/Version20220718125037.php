<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Historically this seeded an "admin" account whose bcrypt hash is public in
 * this repository, which is unacceptable for a published container image.
 *
 * The class is kept, and deliberately empty, because installations from before
 * September 2026 already have it recorded in `db_updates`; deleting it would
 * make Doctrine report an executed migration it no longer knows about.
 *
 * A fresh installation now creates its first administrator from the environment
 * (`greyface:user:create`), and development and test get a throw-away one from
 * `greyface:fixtures:load`.
 */
final class Version20220718125037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Retired: the first admin is created from the environment, not seeded.';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
