<?php

namespace App\Domain\Connect;

/**
 * Where a greylisted entry can be sent, besides the auto-whitelist entry the
 * primary action already creates.
 *
 * The greylist row carries everything each of these needs, so none of them asks
 * the operator for input. What differs is scope, and the names Greyface uses on
 * its list screens hide it:
 *
 *   auto-whitelist  trust this sender *from this source address*. SQLGrey's own
 *                   learned-trust table, and what a normal delivery would have
 *                   written by itself on the retry.
 *   whitelist       never greylist this sender at all, from anywhere. Permanent
 *                   policy, SQLGrey's optout tables.
 *   blacklist       always greylist. SQLGrey's optin tables.
 *
 * Which is why the interface labels these by effect rather than by list name.
 */
enum ListTarget: string
{
    case AutoWhitelistDomain = 'auto-whitelist-domain';
    case WhitelistEmail = 'whitelist-email';
    case WhitelistDomain = 'whitelist-domain';
    case BlacklistEmail = 'blacklist-email';
    case BlacklistDomain = 'blacklist-domain';

    /**
     * Every destination writes to a list that only administrators may edit, so
     * the caller needs the destination's own permission on top of being allowed
     * to touch the greylist row itself.
     */
    public function permission(): string
    {
        return match ($this) {
            self::AutoWhitelistDomain => 'DOMAIN_AUTOWHITE_CREATE',
            self::WhitelistEmail => 'OPTOUT_EMAIL_CREATE',
            self::WhitelistDomain => 'OPTOUT_DOMAIN_CREATE',
            self::BlacklistEmail => 'OPTIN_EMAIL_CREATE',
            self::BlacklistDomain => 'OPTIN_DOMAIN_CREATE',
        };
    }

    /**
     * Whether this destination acts on the sender's whole domain rather than the
     * one address. The interface asks for confirmation on those.
     */
    public function isDomainWide(): bool
    {
        return match ($this) {
            self::AutoWhitelistDomain, self::WhitelistDomain, self::BlacklistDomain => true,
            self::WhitelistEmail, self::BlacklistEmail => false,
        };
    }
}
