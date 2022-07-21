<?php

namespace App\Domain\OptOut\OptOutDomain;

use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
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
