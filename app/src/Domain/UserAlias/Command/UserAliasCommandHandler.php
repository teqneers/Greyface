<?php

namespace App\Domain\UserAlias\Command;

use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\UserAlias\UserAliasFinder;

abstract class UserAliasCommandHandler
{
    use UserAliasFinder;

    public function __construct(UserAliasRepository $userAliasRepository)
    {
        $this->userAliasRepository = $userAliasRepository;
    }
}
