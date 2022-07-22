<?php


namespace App\Domain\OptIn\OptInEmail\Request;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Http\Request\EntityParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class OptInEmailParamConverter extends EntityParamConverter
{
    public function __construct(
        private readonly OptInEmailRepository $optInEmailRepository
    ) {
    }

    protected function loadObject($value, Request $request, ParamConverter $configuration): ?object
    {
        return $this->optInEmailRepository->findById($value);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === OptInEmail::class;
    }
}
