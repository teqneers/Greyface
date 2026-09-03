<?php

namespace App\Tests\Domain\Connect\Validator;

use App\Domain\Connect\Validator\UniqueEntry;
use App\Domain\Connect\Validator\UniqueEntryValidator;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Domain\UserAlias\Command\CreateUserAlias;
use App\Test\ConnectTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * A class-level constraint: it receives the whole command object, not a single
 * property, and looks the greylist entry up by its composite key.
 */
class UniqueEntryValidatorTest extends ConstraintValidatorTestCase
{
    use ConnectTrait;

    private ConnectRepository&MockObject $connectRepository;

    protected function createValidator(): UniqueEntryValidator
    {
        $this->connectRepository = $this->createMock(ConnectRepository::class);

        return new UniqueEntryValidator($this->connectRepository);
    }

    private static function candidate(
        string $name = 'sender',
        string $domain = 'sender.greyface.test',
        string $source = '10.0.0.1',
    ): object {
        return new class ($name, $domain, $source) {
            public function __construct(
                public string $name,
                public string $domain,
                public string $source,
            ) {
            }
        };
    }

    public function testRejectsAForeignConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(self::candidate(), new NotBlank());
    }

    public function testIgnoresNull(): void
    {
        $this->connectRepository->expects(self::never())->method('find');

        $this->validator->validate(null, new UniqueEntry());

        $this->assertNoViolation();
    }

    public function testLooksTheEntryUpByItsCompositeKey(): void
    {
        $this->connectRepository->expects(self::once())
                                ->method('find')
                                ->with([
                                    'name' => 'sender',
                                    'domain' => 'sender.greyface.test',
                                    'source' => '10.0.0.1',
                                ])
                                ->willReturn(null);

        $this->validator->validate(self::candidate(), new UniqueEntry());

        $this->assertNoViolation();
    }

    public function testAcceptsAnEntryThatDoesNotExistYet(): void
    {
        $this->connectRepository->method('find')->willReturn(null);

        $this->validator->validate(self::candidate(), new UniqueEntry());

        $this->assertNoViolation();
    }

    public function testRejectsADuplicateEntry(): void
    {
        $existing = self::createConnect();
        $this->connectRepository->method('find')->willReturn($existing);

        $candidate = self::candidate();
        $constraint = new UniqueEntry();
        $this->validator->validate($candidate, $constraint);

        $this->buildViolation($constraint->message)
             ->setInvalidValue($candidate)
             ->setCause($existing)
             ->assertRaised();
    }

    public function testIsDeclaredAsAClassConstraint(): void
    {
        self::assertSame(UniqueEntry::CLASS_CONSTRAINT, (new UniqueEntry())->getTargets());
    }
}
