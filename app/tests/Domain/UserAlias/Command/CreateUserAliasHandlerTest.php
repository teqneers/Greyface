<?php

namespace App\Tests\Domain\UserAlias\Command;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\UserAlias\Command\CreateUserAlias;
use App\Domain\UserAlias\Command\CreateUserAliasHandler;
use App\Test\UserDomainTrait;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserAliasHandlerTest extends TestCase
{
    use UserDomainTrait;

    private UserRepository&MockObject $userRepository;

    private UserAliasRepository&MockObject $userAliasRepository;

    private CreateUserAliasHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userAliasRepository = $this->createMock(UserAliasRepository::class);
        $this->handler = new CreateUserAliasHandler($this->userRepository, $this->userAliasRepository);
    }

    public function testCreatesAnAliasAttachedToItsUser(): void
    {
        $user = self::createUser();
        $this->userRepository->method('findById')->with($user->getId())->willReturn($user);

        $command = CreateUserAlias::create();
        $command->userId = $user->getId();
        $command->aliasName = 'alias@greyface.test';

        $saved = null;
        $this->userAliasRepository->expects(self::once())
                                  ->method('save')
                                  ->willReturnCallback(function (UserAlias $alias) use (&$saved): UserAlias {
                                      $saved = $alias;

                                      return $alias;
                                  });

        ($this->handler)($command);

        self::assertInstanceOf(UserAlias::class, $saved);
        self::assertSame($command->getId(), $saved->getId());
        self::assertSame($user, $saved->getUser());
        self::assertSame('alias@greyface.test', $saved->getAliasName());
    }

    /**
     * Latent defect, pinned rather than fixed: the handler explicitly allows a
     * command with no userId and passes null to UserAlias::__construct(), which
     * requires a non-null User — so it fatals instead of creating an unassigned
     * alias. It is unreachable through the API only because
     * UserAliasProperties::$userId carries #[Assert\NotBlank], so validation
     * rejects the command first. Change this test when the handler is fixed.
     */
    public function testCreatingAnAliasWithoutAUserCurrentlyFails(): void
    {
        $this->userRepository->expects(self::never())->method('findById');
        $this->userAliasRepository->expects(self::never())->method('save');

        $command = CreateUserAlias::create();
        $command->userId = '';
        $command->aliasName = 'unassigned@greyface.test';

        $this->expectException(\TypeError::class);
        ($this->handler)($command);
    }

    public function testFailsWhenTheReferencedUserDoesNotExist(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $this->userAliasRepository->expects(self::never())->method('save');

        $command = CreateUserAlias::create();
        $command->userId = 'missing-id';
        $command->aliasName = 'alias@greyface.test';

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No user found for id missing-id');

        ($this->handler)($command);
    }

    public function testEveryCommandGetsItsOwnIdentifier(): void
    {
        self::assertNotSame(CreateUserAlias::create()->getId(), CreateUserAlias::create()->getId());
    }
}
