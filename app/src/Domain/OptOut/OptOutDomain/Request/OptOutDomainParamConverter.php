<?php


namespace App\Domain\OptOut\OptOutDomain\Request;

use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use App\Http\Request\EntityParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class OptOutDomainParamConverter extends EntityParamConverter
{
    public function __construct(
        private readonly OptOutDomainRepository $optOutDomainRepository
    ) {
    }

    protected function loadObject($value, Request $request, ParamConverter $configuration): ?object
    {
        return $this->optOutDomainRepository->findById($value);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === OptOutDomain::class;
    }
}
