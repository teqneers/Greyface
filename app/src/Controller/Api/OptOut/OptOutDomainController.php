<?php

namespace App\Controller\Api\OptOut;

use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use App\Messenger\Validation;
use IteratorAggregate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OutOfBoundsException;

#[Route('/api/opt-out/domains')]
class OptOutDomainController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('OPTOUT_DOMAIN_LIST')]
    public function list(
        Request                $request,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $domains = $optOutDomainRepository->findAll($query, $start, $max, $sortBy, boolval($desc));

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
    #[IsGranted('OPTOUT_DOMAIN_CREATE')]
    public function create(
        Request                $request,
        ValidatorInterface     $validator,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $data['domain'] = is_array($data['domain']) ? $data['domain'] : array($data['domain']);

        $domains = [];
        foreach ($data['domain'] as $domain) {
            $domainToCreate = new OptOutDomain($domain);
            $errors = $validator->validate($domainToCreate);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $domains[] = $domainToCreate;
        }

        foreach ($domains as $domain) {
            $optOutDomainRepository->save($domain);
        }

        return new JsonResponse('Data has been added successfully!');
    }

    #[Route('/edit', methods: ['PUT'])]
    #[IsGranted('OPTOUT_DOMAIN_EDIT')]
    public function edit(
        Request                $request,
        ValidatorInterface     $validator,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domainToFind = $data['dynamicID']['domain'] ?? '';
        if ($domainToFind === $data['domain']) { // if old data and new data is same

            $params = ['domain' => $data['domain']];

        } else {
            $optOutDomain = $optOutDomainRepository->findById($domainToFind);
            if (!$optOutDomain) {
                throw new OutOfBoundsException('No Opt-out domain found for ' . $domainToFind);
            }

            $optOutDomain->domain = $data['domain'] ?? '';
            $errors = $validator->validate($optOutDomain);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $domain = $optOutDomainRepository->save($optOutDomain);

            $params = ['domain' => $domain->getDomain()];
        }
        return new JsonResponse($params);
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('OPTOUT_DOMAIN_DELETE')]
    public function delete(
        Request                $request,
        OptOutDomainRepository $optOutDomainRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domain = $data['domain'] ?? '';

        $optOutDomain = $optOutDomainRepository->findById($domain);
        if (!$optOutDomain) {
            throw new OutOfBoundsException('No Opt-out domain found for ' . $domain);
        }
        $optOutDomainRepository->delete($optOutDomain);
        return new JsonResponse('Domain deleted successfully!');
    }
}
