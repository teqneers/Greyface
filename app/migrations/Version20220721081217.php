<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Historically this created SQLGrey's own tables and inserted five sample rows
 * into `connect`. Both are wrong against a real installation: Greyface reads and
 * writes SQLGrey's tables but does not own them, and seeding a live greylist with
 * fake senders corrupts a running mail filter. Its `down()` was worse still — it
 * dropped `connect`, `domain_awl`, `from_awl` and `config` outright.
 *
 * The class is kept, and deliberately empty, because installations from before
 * September 2026 already have it recorded in `db_updates`; deleting it would make
 * Doctrine report an executed migration it no longer knows about.
 *
 * Development and test get the tables and the sample rows from
 * `greyface:fixtures:load` instead, so no SQLGrey installation is needed to work
 * on Greyface. Production expects SQLGrey to have created them already.
 */
final class Version20220721081217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Retired: SQLGrey owns these tables; see greyface:fixtures:load.';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }
}
