<?php

namespace App\Security;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LocalLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly HttpUtils $httpUtils
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('login');
    }

    public function authenticate(Request $request): Passport
    {
        $username = trim($request->request->get('username', ''));
        if (strlen($username) > Security::MAX_USERNAME_LENGTH) {
            throw new BadCredentialsException('Invalid username.');
        }

        if ($request->hasSession() && $session = $request->getSession()) {
            $session->set(Security::LAST_USERNAME, $username);
        }

        $password = $request->request->get('password', '');

        return new Passport(
            new UserBadge(
                $username,
                function (string $userIdentifier) {
                    try {
                        $user = $this->userProvider->loadUserByIdentifier($userIdentifier);
                        if (!$user instanceof User) {
                            throw new AuthenticationServiceException(
                                'The user provider must return a UserInterface object.'
                            );
                        }
                        return $user;
                    } catch (UserNotFoundException $notFound) {
                        throw new BadCredentialsException('Bad credentials.', 0, $notFound);
                    } catch (Exception $repositoryProblem) {
                        throw new AuthenticationServiceException(
                            $repositoryProblem->getMessage(), 0, $repositoryProblem
                        );
                    }
                }
            ),
            new PasswordCredentials($password),
            [
                new RememberMeBadge(),
                new CsrfTokenBadge('login', $request->request->get('csrf_token')),
                new PasswordUpgradeBadge($password, $this->userProvider),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->remove(Security::LAST_USERNAME);
        }

        // check URL or request parameter first
        $targetPath = $request->get('redirect_to');

        if (!$targetPath
            && $request->hasSession()
            && ($session = $request->getSession())
            && ($targetPath = $this->getTargetPath($session, $firewallName))
        ) {
            $this->removeTargetPath($session, $firewallName);
        }
        // fallback to default
        if (!$targetPath) {
            $targetPath = 'app';
        }
        return $this->httpUtils->createRedirectResponse($request, $targetPath);
    }

}
