<?php

namespace App\Domain\User\Request;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Http\Request\EntityValueResolver;

#[AsTargetedValueResolver('app.user')]
class UserValueResolver extends EntityValueResolver
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    protected function loadObject(mixed $value, Request $request, ArgumentMetadata $argument, array $options): ?User
    {
        return $this->userRepository->findById($value, $options['allow_deleted'] ?? false);
    }
}
