<?php

namespace App\Domain\OptIn\OptInEmail;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use OutOfBoundsException;

trait OptInEmailFinder
{
    protected readonly OptInEmailRepository $optInEmailRepository;

    protected function getOptInEmailById(string $id): OptInEmail
    {
        $optInEmail = $this->optInEmailRepository->findById($id);
        if (!$optInEmail) {
            throw new OutOfBoundsException('No OptIn Email found for id ' . $id);
        }
        return $optInEmail;
    }
}
