<?php

namespace App\Domain\UserAlias\Import;

/**
 * What the parser could make of the text: the lines it understood, and the ones
 * it could not. Unreadable lines never stop the rest from being applied — an
 * operator pasting a hundred addresses should not lose ninety-nine of them to
 * one typo — but they are always reported.
 */
final readonly class AliasImportSource
{
    /**
     * @param AliasImportPair[]    $pairs
     * @param AliasImportProblem[] $problems
     */
    public function __construct(
        public array $pairs,
        public array $problems
    ) {
    }
}
