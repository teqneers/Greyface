<?php

namespace App\Domain\OptOut\OptOutEmail;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use OutOfBoundsException;

trait OptOutEmailFinder
{
    protected readonly OptOutEmailRepository $optOutEmailRepository;

    protected function getOptOutEmailById(string $id): OptOutEmail
    {
        $optOutEmail = $this->optOutEmailRepository->findById($id);
        if (!$optOutEmail) {
            throw new OutOfBoundsException('No OptOut Domain found for id ' . $id);
        }
        return $optOutEmail;
    }
}
