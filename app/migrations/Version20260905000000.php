<?php

declare(strict_types=1);

namespace DatabaseUpdates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Aligns Greyface's alias column with SQLGrey's recipient column.
 *
 * The greylist joins tq_aliases.alias_name to connect.rcpt, and MariaDB refuses
 * to compare two columns with different collations. The two sides are created by
 * different software: SQLGrey takes the server default, which is usually
 * utf8mb4_general_ci, while Greyface asked for utf8mb4_unicode_ci. Every greylist
 * request then fails with "Illegal mix of collations".
 *
 * It never showed up before because Greyface used to create SQLGrey's tables
 * itself, so both sides always matched. Now that it correctly leaves them alone,
 * a real SQLGrey database and a fresh Greyface disagree.
 *
 * This reads SQLGrey's collation and changes Greyface's own column to match.
 * SQLGrey's table is only ever read from, never altered. On an installation where
 * the two already agree — which is every database created by the old migrations —
 * it does nothing.
 */
final class Version20260905000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Matches tq_aliases.alias_name to SQLGrey's connect.rcpt collation";
    }

    public function up(Schema $schema): void
    {
        $collations = $this->collations();
        $sqlgrey    = $collations['connect.rcpt'] ?? null;
        $greyface   = $collations['tq_aliases.alias_name'] ?? null;

        $this->skipIf(
            $sqlgrey === null,
            'SQLGrey is not installed in this database yet; nothing to align against.'
        );
        $this->skipIf(
            $greyface === null || $sqlgrey === $greyface,
            sprintf('tq_aliases.alias_name already uses %s.', (string)$sqlgrey)
        );

        $charset = explode('_', (string)$sqlgrey)[0];

        $this->addSql(sprintf(
            'ALTER TABLE tq_aliases MODIFY alias_name VARCHAR(128) CHARACTER SET %s COLLATE %s NOT NULL',
            $charset,
            $sqlgrey
        ));
    }

    public function down(Schema $schema): void
    {
        // Deliberately not reversed. The previous collation was wrong for this
        // database by definition, and restoring it would break the greylist again.
    }

    /**
     * @return array<string, string>
     */
    private function collations(): array
    {
        return $this->connection->fetchAllKeyValue(
            'SELECT CONCAT(TABLE_NAME, ".", COLUMN_NAME), COLLATION_NAME
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND ((TABLE_NAME = "connect" AND COLUMN_NAME = "rcpt")
                  OR (TABLE_NAME = "tq_aliases" AND COLUMN_NAME = "alias_name"))'
        );
    }
}
