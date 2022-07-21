<?php

namespace App\Test;

use App\Domain\Entity\OptInDomain\OptInDomain;

trait OptInOptOutTrait
{
    public static function createOptInDomain(
        string $domain = 'greyface.de'
    ): OptInDomain {
        return OptInDomain::create($domain);
    }
}
