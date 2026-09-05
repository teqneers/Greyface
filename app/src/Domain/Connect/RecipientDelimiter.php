<?php

namespace App\Domain\Connect;

/**
 * Postfix's `recipient_delimiter` lets a mailbox receive tagged mail:
 * with it set to "+", anna+newsletter@example.com is delivered to
 * anna@example.com. Dovecot and most other MTAs do the same.
 *
 * Greyface matches a greylist row to a user by comparing connect.rcpt to the
 * addresses in tq_aliases, and that comparison was exact, so tagged mail
 * belonged to nobody: the recipient could not see it and could not release it,
 * and an administrator had to. That is issue #80.
 *
 * This holds the one rule both sides of that comparison need. The listing has
 * to apply it in SQL, because it runs across every greylist row at once, and
 * ConnectVoter has to apply it in PHP when deciding whether a caller may act on
 * a single row. Keeping the rule in one place is what stops the two from
 * drifting apart and letting somebody act on a row they cannot see, or the
 * reverse.
 *
 * The delimiter is configuration rather than a constant because it is the MTA's
 * setting, not Greyface's, and Greyface cannot read main.cf. Setting it to an
 * empty string turns tag matching off and restores exact matching, which is
 * what a site that does not use address extensions wants: there,
 * anna+newsletter@example.com is a literal mailbox name and need not be anna's.
 */
final readonly class RecipientDelimiter
{
    public function __construct(
        private string $delimiter = '+'
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->delimiter !== '';
    }

    public function delimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * The address a tagged recipient is delivered to: anna+tag@example.com
     * becomes anna@example.com. Anything without a tag comes back unchanged, so
     * callers can compare against this without first asking whether it applies.
     *
     * An address whose local part is nothing but a tag (+tag@example.com) is
     * returned untouched rather than reduced to "@example.com", which is not an
     * address and could never be somebody's alias anyway.
     */
    public function baseAddress(string $address): string
    {
        if (!$this->isEnabled()) {
            return $address;
        }

        $at = strrpos($address, '@');
        if ($at === false) {
            return $address;
        }

        $local = substr($address, 0, $at);
        $tag = strpos($local, $this->delimiter);
        if ($tag === false || $tag === 0) {
            return $address;
        }

        return substr($local, 0, $tag) . substr($address, $at);
    }
}
