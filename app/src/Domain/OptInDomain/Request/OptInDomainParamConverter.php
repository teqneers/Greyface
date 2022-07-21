<?php


namespace App\Domain\OptInDomain\Request;

use App\Domain\Entity\OptInDomain\OptInDomain;
use App\Domain\Entity\OptInDomain\OptInDomainRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Request\EntityParamConverter;

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
