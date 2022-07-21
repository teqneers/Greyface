<?php


namespace App\Domain\OptIn\OptInDomain\Request;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Http\Request\EntityParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class OptInDomainParamConverter extends EntityParamConverter
{
    public function __construct(
        private readonly OptInDomainRepository $optInDomainRepository
    ) {
    }

    protected function loadObject($value, Request $request, ParamConverter $configuration): ?object
    {
        return $this->optInDomainRepository->findById($value);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === OptInDomain::class;
    }
}
