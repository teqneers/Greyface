<?php

namespace App\Test;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;

trait AutoWhiteListTrait
{
    public static function createAutoWhiteListDomain(
        string $domain = 'optin.greyface.de',
        string $source = '121.121.121.121'
    ): DomainAutoWhiteList
    {
        return DomainAutoWhiteList::create($domain, $source);
    }

}
