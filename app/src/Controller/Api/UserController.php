<?php

namespace App\Controller\Api;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Command\CreateUser;
use App\Domain\User\Command\DeleteUser;
use App\Domain\User\Command\SetPassword;
use App\Domain\User\Command\UpdateUser;
use App\Messenger\Validation;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function list(
        Request        $request,
        UserRepository $userRepository
    ): Response
    {
        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $users = $userRepository->findAll(false, $query, $start, $max, $sortBy, boolval($desc));
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
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($users) ? count($users) : $users->count(),
        ]);
    }

    #[Route('/{user}', requirements: ['user' => '%routing.uuid%'], methods: ['GET'])]
    #[IsGranted('USER_SHOW', subject: 'user')]
    #[ParamConverter('user', class: User::class, converter: 'app.user')]
    public function show(
        User $user
    ): Response
    {
        $data = [
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
        return new JsonResponse($data);
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
        $createUser->username = $data['username'] ?? '';
        $createUser->email = $data['email'] ?? '';
        $createUser->role = $data['role'] ?? '';
        $createUser->password = $data['password'] ?? '';
        $errors = $validator->validate($createUser);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
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
        $updateUser->username = $data['username'] ?? '';
        $updateUser->email = $data['email'] ?? '';
        $updateUser->role = $data['role'] ?? '';
        $errors = $validator->validate($updateUser);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $commandBus->dispatch($updateUser);
        $params = ['user' => $updateUser->getId()];
        return new JsonResponse($params);
    }

    #[Route('/{user}/password', requirements: ['user' => '%routing.uuid%'], methods: ['PUT'])]
    #[IsGranted('USER_EDIT', subject: 'user')]
    #[ParamConverter('user', class: User::class, converter: 'app.user')]
    public function setPassword(
        MessageBusInterface $commandBus,
        User $user,
        Request             $request,
        ValidatorInterface  $validator
    ): Response {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $setPassword = SetPassword::set($user);
        $setPassword->password = $data['password'] ?? '';
        $errors = $validator->validate($setPassword);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $commandBus->dispatch($setPassword);
        return new JsonResponse('User password changed successfully!');
    }

    #[Route('/{user}', requirements: ['user' => '%routing.uuid%'], methods: ['DELETE'])]
    #[IsGranted('USER_DELETE', subject: 'user')]
    #[ParamConverter('user', class: User::class, converter: 'app.user')]
    public function delete(
        User                $user,
        MessageBusInterface $commandBus,
    ): Response
    {
        $deleteUser = DeleteUser::delete($user);
        $commandBus->dispatch($deleteUser);
        return new JsonResponse('User deleted successfully!');
    }
}
