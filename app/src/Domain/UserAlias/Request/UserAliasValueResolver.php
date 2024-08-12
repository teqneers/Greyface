<?php

namespace App\Domain\UserAlias\Request;

use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Http\Request\EntityValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsTargetedValueResolver('app.alias')]
class UserAliasValueResolver extends EntityValueResolver
{
    public function __construct(
        private readonly UserAliasRepository $userAliasRepository
    )
    {
    }

    protected function loadObject(mixed $value, Request $request, ArgumentMetadata $argument, array $options): ?UserAlias
    {
        return $this->userAliasRepository->findById($value);
    }
}
