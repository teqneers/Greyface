<?php

namespace App\Domain\UserAlias\Import;

/**
 * One readable line: an address and the account it should belong to. The line
 * number travels with it so a problem found later, when the database is
 * consulted, can still point at the line that caused it.
 */
final readonly class AliasImportPair
{
    public function __construct(
        public int    $line,
        public string $address,
        public string $username
    ) {
    }
}
