<?php

namespace App\Domain\UserAlias\Import;

/**
 * Turns pasted or uploaded text into address/username pairs.
 *
 * The format is deliberately the least a person can be asked to produce:
 * an address, then the account it belongs to, separated by a comma, a tab or
 * spaces. That is what every source of this information can be reduced to with
 * one line of shell, which is why Greyface reads this rather than Postfix's own
 * lookup tables — those may be flat files, hash or lmdb databases, MySQL or
 * LDAP, and reading them would mean reimplementing postmap and still failing on
 * half of them:
 *
 *     postmap -s hash:/etc/postfix/virtual | awk '{print $1 "," $2}'
 *
 * Blank lines and anything after a '#' are ignored, so an exported file can
 * carry comments and a paste can carry blank lines without anyone tidying up.
 *
 * Nothing here touches the database. Whether a username exists or an address is
 * already taken is AliasImporter's business; this only reports what it could not
 * read at all, with line numbers, because an operator fixing a 300-line file
 * needs to know which line to look at.
 */
final readonly class AliasImportParser
{
    /**
     * @param string|null $singleUser when given, the text is a plain list of
     *        addresses that all belong to this account, which is what the alias
     *        dialog's paste box sends. Otherwise every line names its own.
     */
    public function parse(string $text, ?string $singleUser = null): AliasImportSource
    {
        $pairs = [];
        $problems = [];

        foreach (preg_split('/\R/', $text) ?: [] as $index => $raw) {
            $line = trim($this->stripComment($raw));
            if ($line === '') {
                continue;
            }

            $number = $index + 1;
            $fields = preg_split('/\s*[,;\t]\s*|\s+/', $line) ?: [];
            $fields = array_values(array_filter($fields, static fn (string $f): bool => $f !== ''));

            if ($singleUser !== null) {
                if (count($fields) > 1) {
                    $problems[] = new AliasImportProblem($number, $line, 'expectedOneAddress');
                    continue;
                }
                $pairs[] = new AliasImportPair($number, $fields[0], $singleUser);
                continue;
            }

            if (count($fields) < 2) {
                $problems[] = new AliasImportProblem($number, $line, 'missingUsername');
                continue;
            }
            if (count($fields) > 2) {
                $problems[] = new AliasImportProblem($number, $line, 'tooManyFields');
                continue;
            }

            $pairs[] = new AliasImportPair($number, $fields[0], $fields[1]);
        }

        return new AliasImportSource($pairs, $problems);
    }

    /**
     * A '#' only starts a comment at the beginning of a field, so an address is
     * never truncated by one that happens to contain the character.
     */
    private function stripComment(string $line): string
    {
        return preg_replace('/(^|\s)#.*$/', '', $line) ?? $line;
    }
}
