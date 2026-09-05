<?php

namespace App\Domain\UserAlias\Import;

/**
 * A line that could not be applied, and why.
 *
 * The reason is a key rather than a sentence: the console renders it in
 * English, the interface renders it in the operator's language, and neither
 * should be parsing prose to tell one kind of problem from another.
 */
final readonly class AliasImportProblem
{
    public function __construct(
        public int    $line,
        public string $text,
        public string $reason
    ) {
    }

    /**
     * @return array{line: int, text: string, reason: string}
     */
    public function toArray(): array
    {
        return ['line' => $this->line, 'text' => $this->text, 'reason' => $this->reason];
    }
}
