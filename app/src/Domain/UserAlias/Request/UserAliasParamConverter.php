<?php


namespace App\Domain\UserAlias\Request;

use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Request\EntityParamConverter;

class UserAliasParamConverter extends EntityParamConverter
{
    public function __construct(
        private readonly UserAliasRepository $userAliasRepository
    ) {
    }

    protected function loadObject($value, Request $request, ParamConverter $configuration): ?object
    {
        return $this->userAliasRepository->findById($value);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === UserAlias::class;
    }
}
