<?php

namespace App\Controller\Api\OptIn;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Messenger\Validation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/opt-in/domains')]
class OptInDomainController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('OPTIN_DOMAIN_LIST')]
    public function list(
        Request               $request,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $domains = $optInDomainRepository->findAll($start, $max);
        $data = [];
        foreach ($domains as $domain) {
            $data[] = [
                'domain' => $domain->getDomain(),
            ];
        }
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($domains) ? count($domains) : $domains->count(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('OPTIN_DOMAIN_CREATE')]
    public function create(
        Request               $request,
        ValidatorInterface    $validator,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $optinDomain = OptInDomain::create($data['domain'] ?? '');
        $errors = $validator->validate($optinDomain);
        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $optinDomain = $optInDomainRepository->save($optinDomain);
        $params = ['domain' => $optinDomain->getDomain()];
        return new JsonResponse($params, Response::HTTP_CREATED);
    }

    #[Route('/{optInDomain}', methods: ['PUT'])]
    #[IsGranted('OPTIN_DOMAIN_EDIT', subject: 'optInDomain')]
    #[ParamConverter('optInDomain', class: OptInDomain::class, converter: 'app.optInDomain')]
    public function edit(
        OptInDomain           $optInDomain,
        Request               $request,
        ValidatorInterface    $validator,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $optInDomain->domain = $data['domain'] ?? '';
        $errors = $validator->validate($optInDomain);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $domain = $optInDomainRepository->save($optInDomain);

        $params = ['domain' => $domain->getDomain()];
        return new JsonResponse($params);
    }

    #[Route('/{optInDomain}', methods: ['DELETE'])]
    #[IsGranted('OPTIN_DOMAIN_DELETE', subject: 'optInDomain')]
    #[ParamConverter('optInDomain', class: OptInDomain::class, converter: 'app.optInDomain')]
    public function delete(
        OptInDomain           $optInDomain,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $optInDomainRepository->delete($optInDomain);
        return new JsonResponse('Domain deleted successfully!');
    }
}
