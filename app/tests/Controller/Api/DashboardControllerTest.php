<?php

namespace App\Tests\Controller\Api;

use App\Domain\Dashboard\DashboardStatistics;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
    The migrations seed five connect rows (first seen 2022-01-11, 02-11, 03-12
    and two on 07-11) and the admin user; the assertions below rely on that.
*/
class DashboardControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait;

    public function testCountsEveryList(): void
    {
        $admin = self::createAdmin();
        $user = self::createUser();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin, $user);

        $client->request('GET', '/api/dashboard/counts');
        $counts = self::getSuccessfulJsonResponse($client);

        self::assertSame(5, $counts['greylist']);
        self::assertSame(0, $counts['whitelistEmails']);
        self::assertSame(0, $counts['blacklistDomains']);
        // the seeded admin plus the two created here
        self::assertSame(3, $counts['users']);
        self::assertSame(0, $counts['aliases']);
    }

    public function testActivityHasOneBucketPerDayIncludingQuietDays(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/dashboard/activity?days=7');
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(7, $result['days']);
        self::assertCount(7, $result['buckets']);
        $today = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');
        self::assertSame($today, end($result['buckets'])['date']);
        foreach ($result['buckets'] as $bucket) {
            self::assertSame(['date', 'greylisted', 'autoWhitelisted'], array_keys($bucket));
        }
    }

    public function testActivityBucketsCountTheSeededRows(): void
    {
        self::bootKernel();
        $statistics = self::getContainer()->get(DashboardStatistics::class);

        // A window that ends on the day two seeded rows were first seen.
        $buckets = $statistics->activity(3, new DateTimeImmutable('2022-07-11', new DateTimeZone('UTC')));

        self::assertSame(['2022-07-09', '2022-07-10', '2022-07-11'], array_column($buckets, 'date'));
        self::assertSame([0, 0, 2], array_column($buckets, 'greylisted'));
    }

    public function testActivityClampsTheWindow(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/dashboard/activity?days=1000');
        self::assertSame(DashboardStatistics::MAX_DAYS, self::getSuccessfulJsonResponse($client)['days']);
    }

    public function testUsersAreNotAllowed(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);
        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/dashboard/counts');
        self::assertResponseStatusCodeSame(403);
    }
}
