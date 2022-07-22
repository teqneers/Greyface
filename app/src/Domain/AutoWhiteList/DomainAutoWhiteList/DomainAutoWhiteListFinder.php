<?php

namespace App\Domain\AutoWhiteList\DomainAutoWhiteList;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use OutOfBoundsException;

trait DomainAutoWhiteListFinder
{
    protected readonly DomainAutoWhiteListRepository $domainAutoWhiteListRepository;

    protected function getDomainAutoWhiteListById(string $id): DomainAutoWhiteList
    {
        $domain = $this->domainAutoWhiteListRepository->findById($id);
        if (!$domain) {
            throw new OutOfBoundsException('No Domain Auto WhiteList found for id ' . $id);
        }
        return $domain;
    }
}
