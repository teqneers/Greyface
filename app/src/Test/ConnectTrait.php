<?php

namespace App\Test;

use App\Domain\Entity\Connect\Connect;

trait ConnectTrait
{
    /**
     * Builds a greylist entry. The defaults deliberately do not collide with the
     * five rows seeded by migration Version20220721081217, so a test can assert
     * on its own rows without counting the seed data.
     */
    public static function createConnect(
        string $senderName = 'sender',
        string $senderDomain = 'sender.greyface.test',
        string $source = '10.0.0.1',
        string $rcpt = 'recipient@greyface.test',
    ): Connect {
        return Connect::create($senderName, $senderDomain, $source, $rcpt);
    }
}
