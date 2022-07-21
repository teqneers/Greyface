<?php

namespace App\Test;

use App\Domain\Entity\OptInDomain\OptInDomain;
use App\Domain\Entity\OptOutDomain\OptOutDomain;

trait OptInOptOutTrait
{
    public static function createOptInDomain(
        string $domain = 'greyface.de'
    ): OptInDomain {
        return OptInDomain::create($domain);
    }

    public static function createOptOutDomain(
        string $domain = 'greyface.de'
    ): OptOutDomain {
        return OptOutDomain::create($domain);
    }
}
