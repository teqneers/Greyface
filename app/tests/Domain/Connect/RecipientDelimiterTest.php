<?php

namespace App\Tests\Domain\Connect;

use App\Domain\Connect\RecipientDelimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The rule both halves of the greylist matching share: ConnectRepository
 * applies it in SQL across every row, ConnectVoter applies it in PHP to one
 * row. If they ever disagreed, a user would be shown mail the write endpoints
 * then refused to let them touch.
 */
class RecipientDelimiterTest extends TestCase
{
    public static function taggedAddresses(): iterable
    {
        yield 'a tag is stripped' => ['anna+newsletter@example.com', 'anna@example.com'];
        yield 'only the first delimiter counts' => ['anna+a+b@example.com', 'anna@example.com'];
        yield 'an untagged address is unchanged' => ['anna@example.com', 'anna@example.com'];
        yield 'a delimiter in the domain is not a tag' => ['anna@ex+ample.com', 'anna@ex+ample.com'];
        yield 'an empty tag still resolves' => ['anna+@example.com', 'anna@example.com'];
    }

    #[DataProvider('taggedAddresses')]
    public function testResolvesTaggedRecipientsToTheDeliveredAddress(string $rcpt, string $expected): void
    {
        self::assertSame($expected, (new RecipientDelimiter())->baseAddress($rcpt));
    }

    /**
     * "+tag@example.com" has no local part left once the tag is removed.
     * "@example.com" is not an address and could never be somebody's alias, so
     * the address is left alone and only the exact comparison can match it.
     */
    public function testLeavesAnAddressThatIsNothingButATagAlone(): void
    {
        self::assertSame('+tag@example.com', (new RecipientDelimiter())->baseAddress('+tag@example.com'));
    }

    public function testLeavesSomethingThatIsNotAnAddressAlone(): void
    {
        self::assertSame('not-an-address', (new RecipientDelimiter())->baseAddress('not-an-address'));
    }

    /**
     * A site whose MTA has no recipient_delimiter must keep exact matching:
     * there, anna+newsletter@example.com is a literal mailbox name and need not
     * have anything to do with anna.
     */
    public function testAnEmptyDelimiterTurnsTagMatchingOff(): void
    {
        $delimiter = new RecipientDelimiter('');

        self::assertFalse($delimiter->isEnabled());
        self::assertSame('anna+newsletter@example.com', $delimiter->baseAddress('anna+newsletter@example.com'));
    }

    public function testHonoursANonDefaultDelimiter(): void
    {
        $delimiter = new RecipientDelimiter('-');

        self::assertSame('anna@example.com', $delimiter->baseAddress('anna-newsletter@example.com'));
        // "+" is just an ordinary character once the delimiter is something else.
        self::assertSame('anna+newsletter@example.com', $delimiter->baseAddress('anna+newsletter@example.com'));
    }
}
