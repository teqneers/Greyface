<?php

namespace App\Controller;

use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Command\ChangePassword;
use App\Domain\User\Form\ChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/password')]
class PasswordController extends AbstractController
{
    use UserBasedController;

    #[Route('/change', name: 'change_password', methods: ['GET', 'POST'])]
    #[IsGranted('CHANGE_MY_PASSWORD')]
    public function change(
        #[CurrentUser] UserInterface $user,
        Request $request,
        UserRepository $userRepository,
        MessageBusInterface $commandBus
    ): Response
    {
        $user = $this->assertUser($user);
        $userEntity = $userRepository->findById($user->getId());
        if (!$userEntity) {
            throw new AccessDeniedException();
        }

        $changePassword = ChangePassword::change($userEntity);
        $form = $this->createForm(ChangePasswordType::class, $changePassword);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commandBus->dispatch($changePassword);
            $request->getSession()
                ->migrate(true);
            return $this->redirectToRoute('change_password_success');
        }

        return $this->render(
            'password/change.html.twig',
            [
                'user' => $userEntity,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route('/change/success', name: 'change_password_success', methods: ['GET'])]
    #[IsGranted('CHANGE_MY_PASSWORD')]
    public function changeSuccess(): Response
    {
        return $this->render('password/change_success.html.twig');
    }
}
