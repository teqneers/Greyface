<?php

namespace App\Controller\Api;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\User\Command\CreateUser;
use App\Domain\User\Command\DeleteUser;
use App\Domain\User\Command\UpdateUser;
use App\Domain\UserAlias\Command\CreateUserAlias;
use App\Domain\UserAlias\Command\DeleteUserAlias;
use App\Domain\UserAlias\Command\UpdateUserAlias;
use App\Messenger\Validation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users-aliases')]
class UserAliasController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('USER_ALIAS_LIST')]
    public function list(
        Request             $request,
        UserAliasRepository $userAliasRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $userAliases = $userAliasRepository->findAll($start, $max);
        $data = [];
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
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($userAliases) ? count($userAliases) : $userAliases->count(),
        ]);
    }

    #[Route('/{alias}', requirements: ['alias' => '%routing.uuid%'], methods: ['GET'])]
    #[IsGranted('USER_ALIAS_SHOW', subject: 'alias')]
    #[ParamConverter('alias', class: UserAlias::class, converter: 'app.alias')]
    public function show(
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
        Request               $request,
        MessageBusInterface   $commandBus,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface    $validator
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $createUserAlias = CreateUserAlias::create();
        $createUserAlias->userId = $data['user_id'] ?? '';
        $createUserAlias->aliasName = $data['alias_name'] ?? '';
        $errors = $validator->validate($createUserAlias);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $commandBus->dispatch($createUserAlias);

        $params = ['alias' => $createUserAlias->getId()];
        $url = $urlGenerator->generate(
            'app_api_useralias_show',
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($params, Response::HTTP_CREATED, ['Location' => $url]);
    }

    #[Route('/{alias}', requirements: ['alias' => '%routing.uuid%'], methods: ['PUT'])]
    #[IsGranted('USER_ALIAS_EDIT', subject: 'alias')]
    #[ParamConverter('alias', class: UserAlias::class, converter: 'app.alias')]
    public function edit(
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
    #[ParamConverter('alias', class: UserAlias::class, converter: 'app.alias')]
    public function delete(
        UserAlias           $alias,
        MessageBusInterface $commandBus,
    ): Response
    {
        $delete = DeleteUserAlias::delete($alias);
        $commandBus->dispatch($delete);
        return new JsonResponse('User Alias deleted successfully!');
    }
}
