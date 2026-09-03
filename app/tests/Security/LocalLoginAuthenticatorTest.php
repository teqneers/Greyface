<?php

namespace App\Tests\Security;

use App\Domain\Entity\User\User as DomainUser;
use App\Domain\Entity\User\UserRepository;
use App\Security\LocalLoginAuthenticator;
use App\Security\User;
use App\Security\UserProvider;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * The form login entry point. symfony/security-http is under several open
 * advisories and will be upgraded, so this pins the credential handling and the
 * post-login redirect precedence before that happens.
 */
class LocalLoginAuthenticatorTest extends TestCase
{
    use UserDomainTrait;

    private UserRepository&MockObject $userRepository;

    private LocalLoginAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn(string $route): string => match ($route) {
                'login' => '/login',
                default => '/' . $route,
            }
        );

        $this->authenticator = new LocalLoginAuthenticator(
            new UserProvider($this->userRepository),
            $urlGenerator,
            new HttpUtils($urlGenerator)
        );
    }

    private static function loginRequest(array $parameters = [], string $path = '/login'): Request
    {
        $request = Request::create($path, 'POST', $parameters);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    public function testSupportsPostToTheLoginRoute(): void
    {
        self::assertTrue($this->authenticator->supports(self::loginRequest()));
    }

    public function testDoesNotSupportGetToTheLoginRoute(): void
    {
        self::assertFalse($this->authenticator->supports(Request::create('/login', 'GET')));
    }

    public function testDoesNotSupportOtherRoutes(): void
    {
        self::assertFalse($this->authenticator->supports(self::loginRequest([], '/api/users')));
    }

    public function testRejectsAnOverlongUsernameWithoutTouchingTheRepository(): void
    {
        $this->userRepository->expects(self::never())->method('findByUsername');

        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('Invalid username.');

        $this->authenticator->authenticate(self::loginRequest([
            'username' => str_repeat('a', UserBadge::MAX_USERNAME_LENGTH + 1),
            'password' => 'secret',
        ]));
    }

    public function testAcceptsAUsernameExactlyAtTheLengthLimit(): void
    {
        $username = str_repeat('a', UserBadge::MAX_USERNAME_LENGTH);
        $passport = $this->authenticator->authenticate(self::loginRequest([
            'username' => $username,
            'password' => 'secret',
        ]));

        self::assertSame($username, $passport->getBadge(UserBadge::class)->getUserIdentifier());
    }

    public function testTrimsTheUsernameAndRemembersItInTheSession(): void
    {
        $request = self::loginRequest(['username' => "  admin \n", 'password' => 'secret']);

        $passport = $this->authenticator->authenticate($request);

        self::assertSame('admin', $passport->getBadge(UserBadge::class)->getUserIdentifier());
        self::assertSame(
            'admin',
            $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME)
        );
    }

    public function testBuildsAPassportCarryingTheExpectedBadges(): void
    {
        $passport = $this->authenticator->authenticate(
            self::loginRequest(['username' => 'admin', 'password' => 'secret', 'csrf_token' => 'a-token'])
        );

        self::assertTrue($passport->hasBadge(RememberMeBadge::class));
        self::assertTrue($passport->hasBadge(PasswordUpgradeBadge::class));
        self::assertTrue($passport->hasBadge(CsrfTokenBadge::class));

        /** @var PasswordCredentials $credentials */
        $credentials = $passport->getBadge(PasswordCredentials::class);
        self::assertSame('secret', $credentials->getPassword());
    }

    /**
     * An empty identifier is deprecated in Symfony 7.2 and throws in 8.0, so it
     * is rejected up front. The visible outcome is unchanged — an empty username
     * has always failed as bad credentials, previously via a lookup miss.
     */
    public function testRejectsAnEmptyUsername(): void
    {
        $this->userRepository->expects(self::never())->method('findByUsername');

        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('Invalid username.');

        $this->authenticator->authenticate(self::loginRequest());
    }

    public function testRejectsAWhitespaceOnlyUsername(): void
    {
        $this->expectException(BadCredentialsException::class);
        $this->authenticator->authenticate(self::loginRequest(['username' => "   \t ", 'password' => 'secret']));
    }

    public function testDefaultsToAnEmptyPasswordWhenNoneIsSubmitted(): void
    {
        $passport = $this->authenticator->authenticate(self::loginRequest(['username' => 'admin']));

        self::assertSame('', $passport->getBadge(PasswordCredentials::class)->getPassword());
    }

    public function testResolvesAKnownUser(): void
    {
        $domainUser = self::createUser('admin', 'admin@greyface.test');
        $this->userRepository->method('findByUsername')->with('admin')->willReturn($domainUser);

        $passport = $this->authenticator->authenticate(
            self::loginRequest(['username' => 'admin', 'password' => 'secret'])
        );

        $user = $passport->getUser();
        self::assertInstanceOf(User::class, $user);
        self::assertSame('admin', $user->getUserIdentifier());
    }

    /**
     * An unknown user must look identical to a wrong password from the outside,
     * so the provider's UserNotFoundException is remapped to BadCredentials.
     */
    public function testUnknownUsersAreReportedAsBadCredentials(): void
    {
        $this->userRepository->method('findByUsername')->willReturn(null);

        $passport = $this->authenticator->authenticate(
            self::loginRequest(['username' => 'nobody', 'password' => 'secret'])
        );

        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('Bad credentials.');
        $passport->getUser();
    }

    public function testInfrastructureFailuresSurfaceAsAuthenticationServiceErrors(): void
    {
        $this->userRepository->method('findByUsername')
                             ->willThrowException(new RuntimeException('database is on fire'));

        $passport = $this->authenticator->authenticate(
            self::loginRequest(['username' => 'admin', 'password' => 'secret'])
        );

        $this->expectException(AuthenticationServiceException::class);
        $this->expectExceptionMessage('database is on fire');
        $passport->getUser();
    }

    public function testClearsTheRememberedUsernameOnSuccess(): void
    {
        $request = self::loginRequest();
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, 'admin');

        $this->authenticator->onAuthenticationSuccess($request, self::tokenFor(), 'main');

        self::assertFalse($request->getSession()->has(SecurityRequestAttributes::LAST_USERNAME));
    }

    public function testRedirectsToTheApplicationByDefault(): void
    {
        $response = $this->authenticator->onAuthenticationSuccess(self::loginRequest(), self::tokenFor(), 'main');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/app', $response->getTargetUrl());
    }

    public function testAnExplicitRedirectTargetWins(): void
    {
        $request = self::loginRequest(['redirect_to' => '/app/users']);

        $response = $this->authenticator->onAuthenticationSuccess($request, self::tokenFor(), 'main');

        // HttpUtils turns an absolute path into an absolute URL; only the
        // 'app' default below is a route name and stays relative.
        self::assertSame('http://localhost/app/users', $response->getTargetUrl());
    }

    public function testFallsBackToTheStoredTargetPath(): void
    {
        $request = self::loginRequest();
        $request->getSession()->set('_security.main.target_path', '/app/greylist');

        $response = $this->authenticator->onAuthenticationSuccess($request, self::tokenFor(), 'main');

        self::assertSame('http://localhost/app/greylist', $response->getTargetUrl());
        self::assertFalse(
            $request->getSession()->has('_security.main.target_path'),
            'the stored target path should be consumed'
        );
    }

    private static function tokenFor(?DomainUser $user = null): \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
    {
        $securityUser = User::fromUser($user ?? self::createUser());

        return new \Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken(
            $securityUser->getRoles(),
            $securityUser,
            'main'
        );
    }
}
