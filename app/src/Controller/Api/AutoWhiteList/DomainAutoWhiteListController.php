<?php

namespace App\Controller\Api\AutoWhiteList;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use App\Messenger\Validation;
use DateTimeImmutable;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/awl/domains')]
class DomainAutoWhiteListController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('DOMAIN_AUTOWHITE_LIST')]
    public function list(
        Request                       $request,
        DomainAutoWhiteListRepository $domainAutoWhiteListRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $domains = $domainAutoWhiteListRepository->findAll($start, $max);
        $data = [];
        foreach ($domains as $domainAwl) {
            $data[] = [
                'domain' => $domainAwl->getDomain(),
                'source' => $domainAwl->getSource(),
                'first_seen' => $domainAwl->getFirstSeen(),
                'last_seen' => $domainAwl->getLastSeen(),
            ];
        }
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($domains) ? count($domains) : $domains->count(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('DOMAIN_AUTOWHITE_CREATE')]
    public function create(
        Request                       $request,
        ValidatorInterface            $validator,
        DomainAutoWhiteListRepository $domainAutoWhiteListRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domainAwl = DomainAutoWhiteList::create(
            $data['domain'] ?? '',
            $data['source'] ?? '');
        $errors = $validator->validate($domainAwl);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $domainAwl = $domainAutoWhiteListRepository->save($domainAwl);
        $params = ['domain' => $domainAwl->getDomain(), 'source' => $domainAwl->getSource()];
        return new JsonResponse($params, Response::HTTP_CREATED);
    }

    #[Route('/{domain}/{source}', methods: ['PUT'])]
    #[IsGranted('DOMAIN_AUTOWHITE_EDIT')]
    public function edit(
        string                        $domain,
        string                        $source,
        Request                       $request,
        ValidatorInterface            $validator,
        DomainAutoWhiteListRepository $domainAutoWhiteListRepository
    ): Response
    {
        $domainAwl = $domainAutoWhiteListRepository->find([
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$domainAwl) {
            throw new OutOfBoundsException(
                'No White List Domain found for Domain ' . $domain . ' and Source ' . $source
            );
        }
        $body = $request->getContent();
        $data = json_decode($body, true);

        $domainAwl->domain = $data['domain'] ?? '';
        $domainAwl->source = $data['source'] ?? '';
        $errors = $validator->validate($domainAwl);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $domainAwl = $domainAutoWhiteListRepository->save($domainAwl);

        $params = ['domain' => $domainAwl->getDomain(), 'source' => $domainAwl->getSource()];
        return new JsonResponse($params);
    }

    #[Route('/{domain}/{source}/last-seen', methods: ['PUT'])]
    #[IsGranted('DOMAIN_AUTOWHITE_EDIT')]
    public function setLastSeen(
        string                        $domain,
        string                        $source,
        DomainAutoWhiteListRepository $domainAutoWhiteListRepository
    ): Response
    {
        $domainAwl = $domainAutoWhiteListRepository->find([
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$domainAwl) {
            throw new OutOfBoundsException(
                'No White List Domain found for Domain ' . $domain . ' and Source ' . $source
            );
        }

        $domainAwl->lastSeen = new DateTimeImmutable('now');

        $domainAwl = $domainAutoWhiteListRepository->save($domainAwl);

        $params = ['domain' => $domainAwl->getDomain(), 'source' => $domainAwl->getSource()];
        return new JsonResponse($params);
    }

    #[Route('/{domain}/{source}', methods: ['DELETE'])]
    #[IsGranted('DOMAIN_AUTOWHITE_DELETE')]
    public function delete(
        string                        $domain,
        string                        $source,
        DomainAutoWhiteListRepository $domainAutoWhiteListRepository
    ): Response
    {
        $domainAwl = $domainAutoWhiteListRepository->find([
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$domainAwl) {
            throw new OutOfBoundsException(
                'No White List Domain found for Domain ' . $domain . ' and Source ' . $source
            );
        }
        $domainAutoWhiteListRepository->delete($domainAwl);
        return new JsonResponse('Domain deleted successfully!');
    }
}
