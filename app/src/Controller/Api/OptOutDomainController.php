<?php

namespace App\Controller\Api;

use App\Domain\Entity\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOutDomain\OptOutDomainRepository;
use App\Messenger\Validation;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/optout-domains')]
class OptOutDomainController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('OPTOUT_DOMAIN_LIST')]
    public function list(
        Request               $request,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $domains = $optOutDomainRepository->findAll($start, $max);
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
    #[IsGranted('OPTOUT_DOMAIN_CREATE')]
    public function create(
        Request               $request,
        ValidatorInterface    $validator,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $optinDomain = OptOutDomain::create($data['domain'] ?? '');
        $errors = $validator->validate($optinDomain);
        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $optinDomain = $optOutDomainRepository->save($optinDomain);
        $params = ['domain' => $optinDomain->getDomain()];
        return new JsonResponse($params, Response::HTTP_CREATED);
    }

    #[Route('/{optOutDomain}', methods: ['PUT'])]
    #[IsGranted('OPTOUT_DOMAIN_EDIT', subject: 'optOutDomain')]
    #[ParamConverter('optOutDomain', class: OptOutDomain::class, converter: 'app.optOutDomain')]
    public function edit(
        OptOutDomain           $optOutDomain,
        Request               $request,
        ValidatorInterface    $validator,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $domain = $optOutDomainRepository->findById($optOutDomain->getDomain());
        if (!$domain) {
            throw new OutOfBoundsException('No OptOut Domain found for id ' . $optOutDomain->getDomain());
        }
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domain->domain = $data['domain'] ?? '';
        $errors = $validator->validate($domain);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $domain = $optOutDomainRepository->save($domain);

        $params = ['domain' => $domain->getDomain()];
        return new JsonResponse($params);
    }

    #[Route('/{optOutDomain}', methods: ['DELETE'])]
    #[IsGranted('OPTOUT_DOMAIN_DELETE', subject: 'optOutDomain')]
    #[ParamConverter('optOutDomain', class: OptOutDomain::class, converter: 'app.optOutDomain')]
    public function delete(
        OptOutDomain           $optOutDomain,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $domain = $optOutDomainRepository->findById($optOutDomain->getDomain());
        if (!$domain) {
            throw new OutOfBoundsException('No OptOut Domain found for id ' . $optOutDomain->getDomain());
        }
        $optOutDomainRepository->delete($domain);
        return new JsonResponse('Domain deleted successfully!');
    }
}
