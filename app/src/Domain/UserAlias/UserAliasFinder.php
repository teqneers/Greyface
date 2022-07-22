<?php

namespace App\Domain\UserAlias;

use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use OutOfBoundsException;

trait UserAliasFinder
{
    protected readonly UserAliasRepository $userAliasRepository;

    protected function getUserAliasById(string $id): UserAlias
    {
        $userAlias = $this->userAliasRepository->findById($id);
        if (!$userAlias) {
            throw new OutOfBoundsException('No user alias found for id ' . $id);
        }
        return $userAlias;
    }
}
