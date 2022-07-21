<?php


namespace App\Domain\OptOutDomain\Request;

use App\Domain\Entity\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOutDomain\OptOutDomainRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Request\EntityParamConverter;

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
