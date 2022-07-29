<?php

namespace App\Controller\Api\OptIn;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Messenger\Validation;
use IteratorAggregate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OutOfBoundsException;

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
        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $domains = $optInDomainRepository->findAll($query, $start, $max, $sortBy, boolval($desc));

        $count = is_array($domains) ? count($domains) : $domains->count();

        if ($domains instanceof IteratorAggregate) {
            $domains = (array)$domains->getIterator();
        }

        return new JsonResponse([
            'results' => $count === 0 ? [] : $domains,
            'count' => $count,
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

        $data['domain'] = is_array($data['domain']) ? array_unique($data['domain']) : array($data['domain']);

        $domains = [];
        foreach ($data['domain'] as $domain) {
            $domainToCreate = new OptInDomain($domain);
            $errors = $validator->validate($domainToCreate);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $domains[] = $domainToCreate;
        }

        foreach ($domains as $domain) {
            $optInDomainRepository->save($domain);
        }

        return new JsonResponse('Data has been added successfully!');
    }

    #[Route('/{optInDomain}', methods: ['PUT'])]
    #[IsGranted('OPTIN_DOMAIN_EDIT')]
    public function edit(
        Request               $request,
        ValidatorInterface    $validator,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domainToFind = $data['dynamicID']['domain'] ?? '';
        if ($domainToFind === $data['domain']) { // if old data and new data is same

            $params = ['domain' => $data['domain']];

        } else {

            $optInDomain = $optInDomainRepository->findById($domainToFind);
            if (!$optInDomain) {
                throw new OutOfBoundsException('No Opt-In domain found for ' . $domainToFind);
            }

            $optInDomain->domain = $data['domain'] ?? '';
            $errors = $validator->validate($optInDomain);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $domain = $optInDomainRepository->save($optInDomain);

            $params = ['domain' => $domain->getDomain()];
        }
        return new JsonResponse($params);
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('OPTIN_DOMAIN_DELETE')]
    public function delete(
        Request               $request,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domain = $data['domain'] ?? '';

        $optInDomain = $optInDomainRepository->findById($domain);
        if (!$optInDomain) {
            throw new OutOfBoundsException('No Opt-In domain found for ' . $domain);
        }
        $optInDomainRepository->delete($optInDomain);
        return new JsonResponse('Record deleted successfully!');
    }
}
