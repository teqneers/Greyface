<?php

namespace App\Domain\OptOutDomain;

use App\Domain\Entity\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOutDomain\OptOutDomainRepository;
use OutOfBoundsException;

trait OptOutDomainFinder
{
    protected readonly OptOutDomainRepository $optOutDomainRepository;

    protected function getOptOutDomainById(string $id): OptOutDomain
    {
        $optOutDomain = $this->optOutDomainRepository->findById($id);
        if (!$optOutDomain) {
            throw new OutOfBoundsException('No OptOut Domain found for id ' . $id);
        }
        return $optOutDomain;
    }
}
