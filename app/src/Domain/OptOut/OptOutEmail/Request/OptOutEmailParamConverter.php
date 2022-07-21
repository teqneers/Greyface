<?php


namespace App\Domain\OptOut\OptOutEmail\Request;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Http\Request\EntityParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class OptOutEmailParamConverter extends EntityParamConverter
{
    public function __construct(
        private readonly OptOutEmailRepository $optOutEmailRepository
    ) {
    }

    protected function loadObject($value, Request $request, ParamConverter $configuration): ?object
    {
        return $this->optOutEmailRepository->findById($value);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === OptOutEmail::class;
    }
}
