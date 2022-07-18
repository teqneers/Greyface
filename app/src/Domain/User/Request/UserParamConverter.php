<?php


namespace App\Domain\User\Request;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Request\EntityParamConverter;

class UserParamConverter extends EntityParamConverter
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    protected function loadObject($value, Request $request, ParamConverter $configuration): ?object
    {
        return $this->userRepository->findById($value, $configuration->getOptions()['allow_deleted'] ?? false);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === User::class;
    }
}
