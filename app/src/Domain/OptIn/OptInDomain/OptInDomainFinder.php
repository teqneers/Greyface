<?php

namespace App\Domain\OptIn\OptInDomain;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use OutOfBoundsException;

trait OptInDomainFinder
{
    protected readonly OptInDomainRepository $optInDomainRepository;

    protected function getOptInDomainById(string $id): OptInDomain
    {
        $optInDomain = $this->optInDomainRepository->findById($id);
        if (!$optInDomain) {
            throw new OutOfBoundsException('No OptIn Domain found for id ' . $id);
        }
        return $optInDomain;
    }
}
