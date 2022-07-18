<?php


namespace App\Test;

use App\Domain\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait WebTestTrait
{
    use SecurityUserTrait;

    public static function createAuthenticatedClient(
        User $user,
        array $options = [],
        array $server = []
    ): KernelBrowser {
        /** @var KernelBrowser $client */
        $client = static::createClient($options, $server);
        return $client->loginUser(self::createSecurityUser($user), 'main');
    }
}
