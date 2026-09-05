<?php

namespace App\Command;

use App\Domain\UserAlias\Import\AliasImporter;
use App\Domain\UserAlias\Import\AliasImportParser;
use App\Domain\UserAlias\Import\AliasImportResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Assigns mail addresses to accounts from a file, so this does not have to be
 * done one address at a time in the interface.
 *
 * Reads `address username` pairs rather than Postfix's own lookup tables. Those
 * can be flat files, hash or lmdb databases, MySQL or LDAP, and reading them
 * would mean reimplementing postmap and still failing on half of them. One line
 * of shell turns any of them into what this wants:
 *
 *     postmap -s hash:/etc/postfix/virtual | awk '{print $1 "," $2}' > aliases
 *     bin/console greyface:alias:import aliases --prune
 *
 * With --prune it is a sync rather than a one-off, which is what makes it worth
 * putting in cron.
 */
#[AsCommand(
    name: 'greyface:alias:import',
    description: 'Assigns mail addresses to accounts from a two-column file'
)]
class ImportAliasesCommand extends Command
{
    public function __construct(
        private readonly AliasImportParser $parser,
        private readonly AliasImporter     $importer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'A file of "address username" pairs, or - to read standard input'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Report what would change and write nothing'
            )
            ->addOption(
                'prune',
                null,
                InputOption::VALUE_NONE,
                'Also remove addresses the file no longer lists, for the accounts it mentions'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'Treat the file as a plain list of addresses, all belonging to this account'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string)$input->getArgument('file');
        $dryRun = (bool)$input->getOption('dry-run');
        $prune = (bool)$input->getOption('prune');

        $text = $file === '-' ? stream_get_contents(STDIN) : @file_get_contents($file);
        if ($text === false) {
            $io->error(sprintf('Cannot read %s.', $file));

            return Command::FAILURE;
        }

        $source = $this->parser->parse($text, $input->getOption('user'));
        $result = $this->importer->import($source, $prune, $dryRun);

        $this->report($io, $result, $dryRun);

        // Unreadable lines are reported but do not fail the run: an operator
        // syncing from cron wants the other three hundred addresses applied, and
        // a non-zero exit here would look like the import did nothing.
        return Command::SUCCESS;
    }

    private function report(SymfonyStyle $io, AliasImportResult $result, bool $dryRun): void
    {
        if ($result->moves !== []) {
            $io->section('Reassigned to a different account');
            $io->table(
                ['Address', 'From', 'To'],
                array_map(static fn (array $m): array => [$m['address'], $m['from'], $m['to']], $result->moves)
            );
        }

        if ($result->removals !== []) {
            $io->section('No longer listed in the file');
            $io->table(
                ['Address', 'Account'],
                array_map(static fn (array $r): array => [$r['address'], $r['from']], $result->removals)
            );
        }

        if ($result->problems !== []) {
            $io->section('Lines that could not be applied');
            $io->table(
                ['Line', 'Text', 'Problem'],
                array_map(
                    static fn ($p): array => [$p->line, $p->text, self::explain($p->reason)],
                    $result->problems
                )
            );
        }

        $summary = sprintf(
            '%d added, %d reassigned, %d already correct, %d removed, %d skipped',
            $result->created,
            $result->moved,
            $result->unchanged,
            $result->removed,
            count($result->problems)
        );

        if ($dryRun) {
            $io->note('Nothing was written. ' . $summary);
            $io->writeln(' Run again without --dry-run to apply it.');

            return;
        }

        $io->success($summary);
    }

    private static function explain(string $reason): string
    {
        return match ($reason) {
            'missingUsername' => 'no account named; expected "address username"',
            'tooManyFields' => 'more than two columns',
            'expectedOneAddress' => 'expected one address per line with --user',
            'notAnAddress' => 'not a mail address',
            'unknownUser' => 'no such account; import never creates them',
            default => $reason,
        };
    }
}
