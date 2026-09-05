<?php

namespace App\Domain\UserAlias\Import;

/**
 * What an import did, or would do.
 *
 * A dry run fills this in exactly as a real run would, which is the point: the
 * operator reads the same numbers before deciding, rather than a summary
 * produced by different code than the one that will act.
 */
final class AliasImportResult
{
    public int $created = 0;
    public int $moved = 0;
    public int $unchanged = 0;
    public int $removed = 0;

    /** @var array<int, array{address: string, from: string, to: string}> */
    public array $moves = [];

    /** @var array<int, array{address: string, from: string}> */
    public array $removals = [];

    /** @param AliasImportProblem[] $problems */
    public function __construct(
        public array $problems = []
    ) {
    }

    public function addProblem(AliasImportProblem $problem): void
    {
        $this->problems[] = $problem;
    }

    public function addMove(string $address, string $from, string $to): void
    {
        $this->moves[] = ['address' => $address, 'from' => $from, 'to' => $to];
    }

    public function addRemoval(string $address, string $from): void
    {
        $this->removals[] = ['address' => $address, 'from' => $from];
    }

    public function sortProblemsByLine(): void
    {
        usort(
            $this->problems,
            static fn (AliasImportProblem $a, AliasImportProblem $b): int => $a->line <=> $b->line
        );
    }

    public function changesAnything(): bool
    {
        return $this->created > 0 || $this->moved > 0 || $this->removed > 0;
    }

    /**
     * @return array{created:int, moved:int, unchanged:int, removed:int, moves:array, removals:array, problems:array}
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'moved' => $this->moved,
            'unchanged' => $this->unchanged,
            'removed' => $this->removed,
            'moves' => $this->moves,
            'removals' => $this->removals,
            'problems' => array_map(static fn (AliasImportProblem $p): array => $p->toArray(), $this->problems),
        ];
    }
}
