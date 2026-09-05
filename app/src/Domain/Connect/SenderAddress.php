<?php

namespace App\Domain\Connect;

use App\Domain\Entity\Connect\Connect;

/**
 * The sender address a greylist row should be listed under.
 *
 * Not simply name@domain. SQLGrey rewrites the local part before it stores or
 * compares anything, so that one sender behind SRS forwarding, VERP or a
 * bounce-address scheme does not appear as a thousand different senders that
 * each have to earn trust separately. Listing the raw local part would produce
 * an entry that matches exactly one message and never fires again.
 *
 * This mirrors sqlgrey's own deverp_user():
 * https://github.com/jessereynolds/sqlgrey/blob/master/sqlgrey#L1166
 *
 * It moved out of ConnectController when the greylist gained the other list
 * destinations, so the auto-whitelist and the opt-in/opt-out lists cannot end up
 * normalising senders differently.
 */
final readonly class SenderAddress
{
    /**
     * The full address, for the lists that key on one.
     */
    public function of(Connect $entry): string
    {
        [$domain, $name] = $this->parts($entry);

        return $name . '@' . $domain;
    }

    /**
     * @return array{0: string, 1: string} domain, then normalised local part,
     *         truncated to what SQLGrey's own columns hold
     */
    public function parts(Connect $entry): array
    {
        return [
            substr($entry->getDomain(), 0, 255),
            substr($this->deverp($entry->getName()), 0, 64),
        ];
    }

    private function deverp(string $user): string
    {
        // Single-use addresses: SRS, first and subsequent levels of forwarding.
        $user = preg_replace('/^srs0=[^=]+=[^=]+=([^=]+)=([^=]+)$/', 'srs0=#=#=$1=$2', $user);
        $user = preg_replace('/^srs1=[^=]+=([^=]+)(=+)[^=]+=[^=]+=([^=]+)=([^=]+)$/', 'srs1=#=$1$2#=#=$3=$4', $user);

        // The extension mailing lists use for VERP.
        $user = preg_replace('/\+.*$/', '', $user);

        // Frequently used bounce and return masks.
        $user = preg_replace('/((bo|bounce|notice-return|notice-reply)[._-])[0-9a-z-_.]+$/', '$1#', $user);

        // Hexadecimal sequences. At the beginning only when what follows still
        // carries at least four consecutive alphabetic characters.
        return preg_replace(
            '/^[0-9a-f]{2,}(?=[._\/=-].*[a-z]{4,})|(?<=[._\/=-])[0-9a-f]+(?=[._\/=-]|$)/',
            '#',
            $user
        );
    }
}
