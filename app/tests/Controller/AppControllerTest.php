<?php

namespace App\Tests\Controller;

use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use App\Test\WebTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase
{
    use WebTestTrait, DatabaseTestTrait, UserDomainTrait;

    public function testDashboardRedirectsToLoginIfNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request('GET', '/app');

        self::assertResponseRedirects('/login', 302);
    }

    public function testDashboardRendersApplicationIfAuthenticated(): void
    {
        $user   = self::createUser();
        $client = self::createAuthenticatedClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/app');

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorExists('div#app');
    }
}
