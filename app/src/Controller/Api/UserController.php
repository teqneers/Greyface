<?php

namespace App\Controller\Api;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Command\CreateUser;
use App\Domain\User\Command\DeleteUser;
use App\Domain\User\Command\UpdateUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/users')]
class UserController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function list2(
        Request        $request,
        UserRepository $userRepository
    ): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $users = $userRepository->findAll(false, $start, $max);
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'all_roles' => $user->getAllRoles(),
                'is_administrator' => $user->isAdministrator(),
                'is_deleted' => $user->isDeleted(),
                'created_at' => $user->getCreatedAt(),
                'created_by' => $user->getCreatedBy(),
                'updated_at' => $user->getUpdatedAt(),
                'updated_by' => $user->getUpdatedBy(),
            ];
        }
        $response->setContent(json_encode([
            'results' => $data,
            'count' => is_array($users) ? count($users) : $users->count(),
        ]));
        return $response;
    }

    #[Route('/{user}', requirements: ['user' => '%routing.uuid%'], methods: ['GET'])]
    #[IsGranted('USER_SHOW', subject: 'user')]
    #[ParamConverter('user', class: User::class, converter: 'app.user')]
    public function show(
        User $user
    ): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent(json_encode([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'all_roles' => $user->getAllRoles(),
            'is_administrator' => $user->isAdministrator(),
            'is_deleted' => $user->isDeleted(),
            'created_at' => $user->getCreatedAt(),
            'created_by' => $user->getCreatedBy(),
            'updated_at' => $user->getUpdatedAt(),
            'updated_by' => $user->getUpdatedBy(),
        ]));
        return $response;
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('USER_CREATE')]
    public function create(
        Request               $request,
        MessageBusInterface   $commandBus,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface    $validator
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $createUser = CreateUser::create();
        $createUser->username = $data['username'];
        $createUser->email = $data['email'];
        $createUser->role = $data['role'];
        $createUser->password = $data['password'];
        $errors = $validator->validate($createUser);

        if (count($errors) > 0) {
            $violationMessages = [];
            $formErrors = [];
            foreach ($errors as $error) {
                /** @var ConstraintViolationInterface $error */
                $violationMessages[] = $error->getMessage();
                $formErrors[$error->getPropertyPath()] = $error->getMessage();

            }
            return new JsonResponse([
                "errors" => $formErrors,
                'error' => 'Validation failed.' . ' (' . implode(', ', $violationMessages) . ')'],
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $commandBus->dispatch($createUser);

        $params = ['user' => $createUser->getId()];
        $url = $urlGenerator->generate(
            'app_api_user_show',
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($params, Response::HTTP_CREATED, ['Location' => $url]);
    }

    #[Route('/{user}', requirements: ['user' => '%routing.uuid%'], methods: ['PUT'])]
    #[IsGranted('USER_EDIT', subject: 'user')]
    #[ParamConverter('user', class: User::class, converter: 'app.user')]
    public function edit(
        User                $user,
        Request             $request,
        ValidatorInterface  $validator,
        MessageBusInterface $commandBus
    ): Response
    {

        $body = $request->getContent();
        $data = json_decode($body, true);
        $updateUser = UpdateUser::update($user);
        $updateUser->username = $data['username'];
        $updateUser->email = $data['email'];
        $updateUser->role = $data['role'];
        $errors = $validator->validate($updateUser);

        if (count($errors) > 0) {
            $violationMessages = [];
            $formErrors = [];
            foreach ($errors as $error) {
                /** @var ConstraintViolationInterface $error */
                $violationMessages[] = $error->getMessage();
                $formErrors[$error->getPropertyPath()] = $error->getMessage();

            }
            return new JsonResponse([
                "errors" => $formErrors,
                'error' => 'Validation failed.' . ' (' . implode(', ', $violationMessages) . ')'],
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $commandBus->dispatch($updateUser);
        $params = ['user' => $updateUser->getId()];
        return new JsonResponse($params, Response::HTTP_OK);
    }

    #[Route('/{user}', requirements: ['user' => '%routing.uuid%'], methods: ['DELETE'])]
    #[IsGranted('USER_DELETE', subject: 'user')]
    #[ParamConverter('user', class: User::class, converter: 'app.user')]
    public function delete(
        User $user,
        MessageBusInterface $commandBus,
    ): Response {
        $deleteUser = DeleteUser::softDelete($user);
        $commandBus->dispatch($deleteUser);
        return new JsonResponse('User deleted successfully!', Response::HTTP_OK);
    }
}
