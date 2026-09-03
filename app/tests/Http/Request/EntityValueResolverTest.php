<?php

namespace App\Tests\Http\Request;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Request\UserValueResolver;
use App\Http\Request\EntityValueResolver;
use App\Http\Request\MapEntityOptions;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Turns a route parameter into an entity. Its NotFoundHttpException branch is
 * what produces a 404 for an unknown id, and no test in the suite reached it.
 */
class EntityValueResolverTest extends TestCase
{
    use UserDomainTrait;

    /**
     * @param array<int, object> $attributes
     */
    private static function argument(
        string $name = 'user',
        ?string $type = User::class,
        bool $hasDefaultValue = false,
        mixed $defaultValue = null,
        bool $isNullable = false,
        array $attributes = [],
    ): ArgumentMetadata {
        return new ArgumentMetadata($name, $type, false, $hasDefaultValue, $defaultValue, $isNullable, $attributes);
    }

    private static function requestWith(array $attributes = []): Request
    {
        $request = Request::create('/api/users/an-id');
        $request->attributes->add($attributes);

        return $request;
    }

    /**
     * @param callable|null $loader receives ($value, $request, $argument, $options)
     */
    private static function resolver(?callable $loader = null, ?array &$seenOptions = null): EntityValueResolver
    {
        return new class ($loader, $seenOptions) extends EntityValueResolver {
            public function __construct(
                private $loader,
                private &$seenOptions,
            ) {
            }

            protected function loadObject(
                mixed $value,
                Request $request,
                ArgumentMetadata $argument,
                array $options,
            ): ?object {
                $this->seenOptions = $options;

                return $this->loader === null ? null : ($this->loader)($value, $request, $argument, $options);
            }
        };
    }

    public function testResolvesTheEntityForAPresentRouteParameter(): void
    {
        $user = self::createUser();
        $resolver = self::resolver(static fn(): User => $user);

        $resolved = $resolver->resolve(self::requestWith(['user' => 'an-id']), self::argument());

        self::assertSame([$user], $resolved);
    }

    public function testPassesTheRouteValueThroughToTheLoader(): void
    {
        $seen = null;
        $resolver = self::resolver(function (mixed $value) use (&$seen): ?User {
            $seen = $value;

            return null;
        });

        $resolver->resolve(self::requestWith(['user' => 'an-id']), self::argument(isNullable: true));

        self::assertSame('an-id', $seen);
    }

    public function testForwardsMapEntityOptionsToTheLoader(): void
    {
        $options = [];
        $resolver = self::resolver(static fn(): ?User => null, $options);

        $resolver->resolve(
            self::requestWith(['user' => 'an-id']),
            self::argument(isNullable: true, attributes: [new MapEntityOptions(['allow_deleted' => true])])
        );

        self::assertSame(['allow_deleted' => true], $options);
    }

    public function testPassesEmptyOptionsWhenTheAttributeIsAbsent(): void
    {
        $options = ['not-empty'];
        $resolver = self::resolver(static fn(): ?User => null, $options);

        $resolver->resolve(self::requestWith(['user' => 'an-id']), self::argument(isNullable: true));

        self::assertSame([], $options);
    }

    public function testResolvesToNullWhenTheRouteParameterIsMissingAndTheArgumentIsNullable(): void
    {
        $resolver = self::resolver(static fn(): ?User => self::fail('the loader must not run'));

        self::assertSame([null], $resolver->resolve(self::requestWith(), self::argument(isNullable: true)));
    }

    public function testResolvesToNullWhenTheRouteParameterIsMissingAndThereIsNoDefault(): void
    {
        $resolver = self::resolver(static fn(): ?User => self::fail('the loader must not run'));

        self::assertSame([null], $resolver->resolve(self::requestWith(), self::argument()));
    }

    public function testFallsBackToTheDefaultWhenTheRouteParameterIsMissing(): void
    {
        $default = self::createUser('default', 'default@greyface.test');
        $resolver = self::resolver(static fn(): ?User => self::fail('the loader must not run'));

        $resolved = $resolver->resolve(
            self::requestWith(),
            self::argument(hasDefaultValue: true, defaultValue: $default)
        );

        self::assertSame([$default], $resolved);
    }

    public function testResolvesToNullWhenTheEntityIsMissingButTheArgumentIsNullable(): void
    {
        $resolver = self::resolver(static fn(): ?User => null);

        $resolved = $resolver->resolve(self::requestWith(['user' => 'ghost']), self::argument(isNullable: true));

        self::assertSame([null], $resolved);
    }

    public function testFallsBackToTheDefaultWhenTheEntityIsMissing(): void
    {
        $default = self::createUser('default', 'default@greyface.test');
        $resolver = self::resolver(static fn(): ?User => null);

        $resolved = $resolver->resolve(
            self::requestWith(['user' => 'ghost']),
            self::argument(hasDefaultValue: true, defaultValue: $default)
        );

        self::assertSame([$default], $resolved);
    }

    public function testThrowsNotFoundWhenTheEntityIsMissingAndTheArgumentIsRequired(): void
    {
        $resolver = self::resolver(static fn(): ?User => null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            sprintf('Item of class %s not found for parameter user with value ghost.', User::class)
        );

        $resolver->resolve(self::requestWith(['user' => 'ghost']), self::argument());
    }

    public function testUserValueResolverLooksTheUserUpById(): void
    {
        $user = self::createUser();
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
                   ->method('findById')
                   ->with('an-id', false)
                   ->willReturn($user);

        $resolved = (new UserValueResolver($repository))
            ->resolve(self::requestWith(['user' => 'an-id']), self::argument());

        self::assertSame([$user], $resolved);
    }

    public function testUserValueResolverHonoursTheAllowDeletedOption(): void
    {
        $user = self::createUser()->delete();
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
                   ->method('findById')
                   ->with('an-id', true)
                   ->willReturn($user);

        $resolved = (new UserValueResolver($repository))->resolve(
            self::requestWith(['user' => 'an-id']),
            self::argument(attributes: [new MapEntityOptions(['allow_deleted' => true])])
        );

        self::assertSame([$user], $resolved);
    }
}
