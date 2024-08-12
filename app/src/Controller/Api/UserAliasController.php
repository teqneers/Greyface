<?php

namespace App\Controller\Api;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\UserAlias\Command\DeleteUserAlias;
use App\Domain\UserAlias\Command\UpdateUserAlias;
use App\Messenger\Validation;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users-aliases')]
class UserAliasController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('USER_ALIAS_LIST')]
    public function list(
        Request             $request,
        UserAliasRepository $userAliasRepository,
        UserRepository      $userRepository
    ): Response
    {

        $user = null;
        $userFilter = $request->query->get('user');
        if ($userFilter) {
            $user = $userRepository->findById($userFilter);
        }

        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $userAliases = $userAliasRepository->findAll($user, $query, $start, $max, $sortBy, boolval($desc));
        $data = [];
        if ($userAliases) {
            foreach ($userAliases as $alias) {
                $user = $alias->getUser();
                $data[] = [
                    'id' => $alias->getId(),
                    'user' => [
                        'id' => $user->getId(),
                        'username' => $user->getUsername(),
                        'email' => $user->getEmail(),
                        'role' => $user->getRole(),
                        'all_roles' => $user->getAllRoles(),
                        'is_administrator' => $user->isAdministrator(),
                        'is_deleted' => $user->isDeleted(),
                    ],
                    'alias_name' => $alias->getAliasName(),
                ];
            }
        }
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($userAliases) ? count($userAliases) : $userAliases->count(),
        ]);
    }

    #[Route('/{alias}', requirements: ['alias' => '%routing.uuid%'], methods: ['GET'])]
    #[IsGranted('USER_ALIAS_SHOW', subject: 'alias')]
    public function show(
        #[ValueResolver('app.alias')]
        UserAlias $alias
    ): Response
    {
        $user = $alias->getUser();
        $data = [
            'id' => $alias->getId(),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'all_roles' => $user->getAllRoles(),
                'is_administrator' => $user->isAdministrator(),
                'is_deleted' => $user->isDeleted(),
            ],
            'alias_name' => $alias->getAliasName(),
        ];
        return new JsonResponse($data);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('USER_ALIAS_CREATE')]
    public function create(
        Request             $request,
        UserRepository      $userRepository,
        UserAliasRepository $userAliasRepository,
        ValidatorInterface  $validator
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $user = $data['user_id'] ? $userRepository->findById($data['user_id']) : null;
        $data['alias_name'] = is_array($data['alias_name']) ? array_unique($data['alias_name']) : array($data['alias_name']);

        $aliases = [];
        foreach ($data['alias_name'] as $alias) {
            $aliasToCreate = new UserAlias(
                Uuid::uuid4()->toString(),
                $user,
                $alias
            );
            $errors = $validator->validate($aliasToCreate);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $aliases[] = $aliasToCreate;
        }

        foreach ($aliases as $alias) {
            $userAliasRepository->save($alias);
        }

        return new JsonResponse('Alias has been added successfully!');
    }

    #[Route('/{alias}', requirements: ['alias' => '%routing.uuid%'], methods: ['PUT'])]
    #[IsGranted('USER_ALIAS_EDIT', subject: 'alias')]
    public function edit(
        #[ValueResolver('app.alias')]
        UserAlias           $alias,
        Request             $request,
        ValidatorInterface  $validator,
        MessageBusInterface $commandBus
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $updateUserAlias = UpdateUserAlias::update($alias);
        $updateUserAlias->userId = $data['user_id'] ?? '';
        $updateUserAlias->aliasName = $data['alias_name'] ?? '';
        $errors = $validator->validate($updateUserAlias);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $commandBus->dispatch($updateUserAlias);
        $params = ['alias' => $updateUserAlias->getId()];
        return new JsonResponse($params);
    }

    #[Route('/{alias}', requirements: ['alias' => '%routing.uuid%'], methods: ['DELETE'])]
    #[IsGranted('USER_ALIAS_DELETE', subject: 'alias')]
    public function delete(
        #[ValueResolver('app.alias')]
        UserAlias           $alias,
        MessageBusInterface $commandBus,
    ): Response
    {
        $delete = DeleteUserAlias::delete($alias);
        $commandBus->dispatch($delete);
        return new JsonResponse('User Alias deleted successfully!');
    }
}
