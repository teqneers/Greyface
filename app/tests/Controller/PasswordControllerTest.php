<?php

namespace App\Tests\Controller;

use App\Domain\Entity\User\User;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use App\Test\WebTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PasswordControllerTest extends WebTestCase
{
    use WebTestTrait, DatabaseTestTrait, UserDomainTrait;

    public function testChangePasswordRedirectsToLoginIfNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request('GET', '/password/change');

        self::assertResponseRedirects('/login', 302);
    }

    public function testShowsChangePasswordForm(): void
    {
        $user   = self::createUser();
        $client = self::createAuthenticatedClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/password/change');

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextSame('h3', 'Change Password');
    }

    public function testChangePassword(): void
    {
        $user   = self::createUser();
        $client = self::createAuthenticatedClient($user);

        self::setEncodedUserPassword($user, 'test');
        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/password/change');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h3', 'Change Password');

        $client->submitForm(
            'Change Password',
            [
                'change_password[currentPassword]'  => 'test',
                'change_password[password][first]'  => 'Werbung1mBr1efk45ten',
                'change_password[password][second]' => 'Werbung1mBr1efk45ten',
            ]
        );

        self::assertResponseRedirects('/password/change/success');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h3', 'Password changed');

        self::clearEntityManager();
        /** @var User|null $user */
        [$user] = self::reloadDatabaseEntities($user);
        self::assertNotNull($user);
        self::assertUserPasswordEquals('Werbung1mBr1efk45ten', $user->getPassword());
    }

}
