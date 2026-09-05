<?php

namespace App\Tests\Domain\UserAlias\Import;

use App\Domain\UserAlias\Import\AliasImportParser;
use PHPUnit\Framework\TestCase;

/**
 * The parser only decides what a line says, never whether it can be applied.
 * Whether an account exists or an address is taken is the importer's business,
 * so everything here is pure and needs no database.
 */
class AliasImportParserTest extends TestCase
{
    private AliasImportParser $parser;

    protected function setUp(): void
    {
        $this->parser = new AliasImportParser();
    }

    public function testReadsCommaSeparatedPairs(): void
    {
        $source = $this->parser->parse("anna@example.com,anna\nbob@example.com,bob");

        self::assertCount(2, $source->pairs);
        self::assertSame([], $source->problems);
        self::assertSame('anna@example.com', $source->pairs[0]->address);
        self::assertSame('anna', $source->pairs[0]->username);
    }

    /**
     * `postmap -s` emits tab-separated columns, so a paste straight out of it
     * has to work without anyone reformatting it first.
     */
    public function testReadsWhitespaceAndSemicolonSeparatedPairs(): void
    {
        $source = $this->parser->parse("anna@example.com\tanna\nbob@example.com   bob\ncarl@example.com; carl");

        self::assertCount(3, $source->pairs);
        self::assertSame([], $source->problems);
        self::assertSame('bob', $source->pairs[1]->username);
        self::assertSame('carl', $source->pairs[2]->username);
    }

    public function testIgnoresBlankLinesAndComments(): void
    {
        $source = $this->parser->parse("# exported from postfix\n\nanna@example.com,anna\n\n  # trailing note\n");

        self::assertCount(1, $source->pairs);
        self::assertSame([], $source->problems);
    }

    public function testStripsATrailingCommentWithoutTruncatingAnAddress(): void
    {
        $source = $this->parser->parse('anna@example.com,anna  # the boss');

        self::assertCount(1, $source->pairs);
        self::assertSame('anna', $source->pairs[0]->username);
    }

    public function testReportsALineWithNoUsername(): void
    {
        $source = $this->parser->parse("anna@example.com,anna\njustanaddress@example.com");

        self::assertCount(1, $source->pairs);
        self::assertCount(1, $source->problems);
        self::assertSame(2, $source->problems[0]->line);
        self::assertSame('missingUsername', $source->problems[0]->reason);
    }

    public function testReportsALineWithTooManyFields(): void
    {
        $source = $this->parser->parse('anna@example.com,anna,extra');

        self::assertSame([], $source->pairs);
        self::assertSame('tooManyFields', $source->problems[0]->reason);
    }

    /**
     * One bad line must not cost the operator the other ninety-nine, and the
     * line number is what makes a long paste fixable.
     */
    public function testKeepsTheGoodLinesAndNumbersTheBadOnes(): void
    {
        $source = $this->parser->parse("anna@example.com,anna\noops\nbob@example.com,bob");

        self::assertCount(2, $source->pairs);
        self::assertCount(1, $source->problems);
        self::assertSame(2, $source->problems[0]->line);
        self::assertSame(3, $source->pairs[1]->line);
    }

    /**
     * The alias dialog's paste box: a plain list of addresses that all belong to
     * the account already chosen in the form.
     */
    public function testASingleUserListNeedsNoUsernameColumn(): void
    {
        $source = $this->parser->parse("anna@example.com\nanna+shop@example.com", singleUser: 'anna');

        self::assertCount(2, $source->pairs);
        self::assertSame('anna', $source->pairs[0]->username);
        self::assertSame('anna', $source->pairs[1]->username);
    }

    public function testASingleUserListRejectsASecondColumn(): void
    {
        $source = $this->parser->parse('anna@example.com,somebody-else', singleUser: 'anna');

        self::assertSame([], $source->pairs);
        self::assertSame('expectedOneAddress', $source->problems[0]->reason);
    }

    public function testHandlesWindowsLineEndings(): void
    {
        $source = $this->parser->parse("anna@example.com,anna\r\nbob@example.com,bob\r\n");

        self::assertCount(2, $source->pairs);
        self::assertSame('bob@example.com', $source->pairs[1]->address);
    }
}
