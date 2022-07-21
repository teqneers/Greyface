<?php

namespace App\Test;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;

trait OptInOptOutTrait
{
    public static function createOptInDomain(
        string $domain = 'optin.greyface.de'
    ): OptInDomain
    {
        return OptInDomain::create($domain);
    }

    public static function createOptOutDomain(
        string $domain = 'optout.greyface.de'
    ): OptOutDomain
    {
        return OptOutDomain::create($domain);
    }

    public static function createOptInEmail(
        string $email = 'optin@email.de'
    ): OptInEmail
    {
        return OptInEmail::create($email);
    }

    public static function createOptOutEmail(
        string $email = 'optout@email.de'
    ): OptOutEmail
    {
        return OptOutEmail::create($email);
    }
}
