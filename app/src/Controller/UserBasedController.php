<?php

namespace App\Controller;

use App\Domain\User\UserInterface as DomainUserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserBasedController
{
    private function assertUser(UserInterface $user): DomainUserInterface&UserInterface
    {
        if (!$user instanceof DomainUserInterface) {
            throw new AccessDeniedException();
        }
        return $user;
    }
}
