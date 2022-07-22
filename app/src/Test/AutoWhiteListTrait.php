<?php

namespace App\Test;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;

trait AutoWhiteListTrait
{
    public static function createAutoWhiteListDomain(
        string $domain = 'greyface.de',
        string $source = '121.121.121.121'
    ): DomainAutoWhiteList
    {
        return DomainAutoWhiteList::create($domain, $source);
    }

    public static function createAutoWhiteListEmail(
        string $senderName = 'whitelist@email.de',
        string $domain = 'greyface.de',
        string $source = '121.121.121.121'
    ): EmailAutoWhiteList
    {
        return EmailAutoWhiteList::create($senderName, $domain, $source);
    }

}
