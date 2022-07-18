<?php

namespace App\Controller;

use App\Domain\Entity\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class AppController extends AbstractController
{
    use UserBasedController;

    #[Route('/app/{route<.*>?}', name: 'app', methods: ['GET'])]
    public function dashboard(
        #[CurrentUser] UserInterface $user,
        Request $request,
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        LogoutUrlGenerator $logoutUrlGenerator
    ): Response {
        $user       = $this->assertUser($user);
        $userEntity = $userRepository->findById($user->getId());
        if (!$userEntity) {
            throw new AccessDeniedException();
        }

        $config = [
            'apiUrl'            => $request->getUriForPath('/api'),
            'baseUrl'           => $urlGenerator->generate('app'),
            'logoutUrl'         => $logoutUrlGenerator->getLogoutPath(),
            'changePasswordUrl' => $urlGenerator->generate('change_password'),
            'user'              => $userEntity,
        ];

        return $this->render(
            'app.html.twig',
            [
                'config' => $config,
            ]
        );
    }
}
