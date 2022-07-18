<?php

namespace App\Tests\Controller;

use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    use DatabaseTestTrait, UserDomainTrait, ApiTestTrait;

    public function testShowsLoginForm(): void
    {
        $client = self::createClient();

        $client->request('GET', '/login');

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextSame('h3', 'Welcome to Greyface');
    }

    public function testLoginExistingUser(): void
    {
        $client = self::createClient();

        $password = 'test';
        $user = self::setEncodedUserPassword(self::createUser(), $password);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/login');
        $client->submitForm(
            'Log In',
            [
                'username' => $user->getUsername(),
                'password' => $password,
            ]
        );

        self::assertResponseRedirects('http://localhost/app', 302);
    }

    public function testLoginNonExistingUser(): void
    {
        $client = self::createClient();

        $password = 'test';
        $user = self::setEncodedUserPassword(self::createUser(), $password);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/login');
        $client->submitForm(
            'Log In',
            [
                'username' => 'doesnotexist',
                'password' => $password,
            ]
        );

        self::assertResponseRedirects('/login', 302);

        $client->followRedirect();
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextSame('div.alert-danger', 'Invalid credentials.');
        self::assertInputValueSame('username', 'doesnotexist');
    }

    public function testLoginExistingUserWithWrongPassword(): void
    {
        $client = self::createClient();

        $password = 'test';
        $user = self::setEncodedUserPassword(self::createUser(), $password);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/login');
        $client->submitForm(
            'Log In',
            [
                'username' => $user->getUsername(),
                'password' => 'wrongpassword',
            ]
        );

        self::assertResponseRedirects('/login', 302);

        $client->followRedirect();
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextSame('div.alert-danger', 'Invalid credentials.');
        self::assertInputValueSame('username', $user->getUsername());
    }

    public function testLoginRedirectsToAppIfAlreadyAuthenticated(): void
    {
        $user = self::createUser();
        $client = self::createAuthenticatedClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/login');

        self::assertResponseRedirects('/app', 302);
    }

    public function testLoginRespondsWithSpecialJsonMessageForAjaxClients(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/login', [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('login', $result);
        self::assertEquals('http://localhost/login', $result['login']);
    }
}
