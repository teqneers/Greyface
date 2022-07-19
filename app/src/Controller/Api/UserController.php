<?php

namespace App\Controller\Api;

use App\Domain\Entity\User\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
