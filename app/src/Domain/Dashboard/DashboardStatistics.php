<?php

namespace App\Domain\Dashboard;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Connection;

/**
 * Read-only counts for the dashboard, straight from SQLGrey's tables and
 * Greyface's user table. Plain SQL on purpose: these are aggregates over
 * tables Doctrine does not own, and the ORM adds nothing here.
 */
class DashboardStatistics
{
    public const MAX_DAYS = 90;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        $count = fn(string $sql): int => (int)$this->connection->fetchOne($sql);

        return [
            'greylist' => $count('SELECT COUNT(*) FROM connect'),
            'autoWhitelistEmails' => $count('SELECT COUNT(*) FROM from_awl'),
            'autoWhitelistDomains' => $count('SELECT COUNT(*) FROM domain_awl'),
            'whitelistEmails' => $count('SELECT COUNT(*) FROM optout_email'),
            'whitelistDomains' => $count('SELECT COUNT(*) FROM optout_domain'),
            'blacklistEmails' => $count('SELECT COUNT(*) FROM optin_email'),
            'blacklistDomains' => $count('SELECT COUNT(*) FROM optin_domain'),
            'users' => $count('SELECT COUNT(*) FROM tq_users WHERE deleted_at IS NULL'),
            'aliases' => $count('SELECT COUNT(*) FROM tq_aliases'),
        ];
    }

    /**
     * One row per calendar day, oldest first, with the entries still pending
     * that were first seen that day and the auto-whitelist rows last seen
     * (i.e. senders accepted) that day. Days without activity are present with zeros.
     *
     * @return list<array{date: string, greylisted: int, autoWhitelisted: int}>
     */
    public function activity(int $days, ?DateTimeImmutable $today = null): array
    {
        $days = max(1, min(self::MAX_DAYS, $days));
        $today = ($today ?? new DateTimeImmutable('now', new DateTimeZone('UTC')))->setTime(0, 0);
        $since = $today->sub(new DateInterval('P' . ($days - 1) . 'D'));

        $buckets = [];
        for ($day = $since; $day <= $today; $day = $day->add(new DateInterval('P1D'))) {
            $buckets[$day->format('Y-m-d')] = ['date' => $day->format('Y-m-d'), 'greylisted' => 0, 'autoWhitelisted' => 0];
        }

        $fill = function (string $sql, string $key) use (&$buckets, $since): void {
            foreach ($this->connection->fetchAllAssociative($sql, ['since' => $since->format('Y-m-d 00:00:00')]) as $row) {
                $date = (string)$row['day'];
                if (isset($buckets[$date])) {
                    $buckets[$date][$key] = (int)$row['total'];
                }
            }
        };
        $fill('SELECT DATE(first_seen) AS day, COUNT(*) AS total FROM connect WHERE first_seen >= :since GROUP BY DATE(first_seen)', 'greylisted');
        $fill('SELECT DATE(last_seen) AS day, COUNT(*) AS total FROM from_awl WHERE last_seen >= :since GROUP BY DATE(last_seen)', 'autoWhitelisted');

        return array_values($buckets);
    }
}
