<?php

namespace App\Controller\Api\AutoWhiteList;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Messenger\Validation;
use DateTimeImmutable;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/awl/emails')]
class EmailAutoWhiteListController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('EMAIL_AUTOWHITE_LIST')]
    public function list(
        Request                      $request,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $emails = $emailAutoWhiteListRepository->findAll($start, $max);
        $data = [];
        foreach ($emails as $emailAwl) {
            $data[] = [
                'name' => $emailAwl->getName(),
                'domain' => $emailAwl->getDomain(),
                'source' => $emailAwl->getSource(),
                'first_seen' => $emailAwl->getFirstSeen(),
                'last_seen' => $emailAwl->getLastSeen(),
            ];
        }
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($emails) ? count($emails) : $emails->count(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('EMAIL_AUTOWHITE_CREATE')]
    public function create(
        Request                      $request,
        ValidatorInterface           $validator,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $emailAwl = EmailAutoWhiteList::create(
            $data['name'] ?? '',
            $data['domain'] ?? '',
            $data['source'] ?? '');
        $errors = $validator->validate($emailAwl);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $emailAwl = $emailAutoWhiteListRepository->save($emailAwl);
        $params = ['name' => $emailAwl->getName(), 'domain' => $emailAwl->getDomain(), 'source' => $emailAwl->getSource()];
        return new JsonResponse($params, Response::HTTP_CREATED);
    }

    #[Route('/edit', methods: ['PUT'])]
    #[IsGranted('EMAIL_AUTOWHITE_EDIT')]
    public function edit(
        Request                      $request,
        ValidatorInterface           $validator,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $name = $data['dynamicId']['name'];
        $domain = $data['dynamicId']['domain'];
        $source = $data['dynamicId']['source'];

        $emailAwl = $emailAutoWhiteListRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$emailAwl) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $name . ', Domain ' . $domain . ' and Source ' . $source
            );
        }

        $emailAwl->name = $data['name'] ?? '';
        $emailAwl->domain = $data['domain'] ?? '';
        $emailAwl->source = $data['source'] ?? '';
        $errors = $validator->validate($emailAwl);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $emailAwl = $emailAutoWhiteListRepository->save($emailAwl);

        $params = ['name' => $emailAwl->getName(), 'domain' => $emailAwl->getDomain(), 'source' => $emailAwl->getSource()];
        return new JsonResponse($params);
    }

    #[Route('/last-seen', methods: ['PUT'])]
    #[IsGranted('EMAIL_AUTOWHITE_EDIT')]
    public function setLastSeen(
        Request                      $request,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $name = $data['dynamicId']['name'];
        $domain = $data['dynamicId']['domain'];
        $source = $data['dynamicId']['source'];

        $emailAwl = $emailAutoWhiteListRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$emailAwl) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $name . ', Domain ' . $domain . ' and Source ' . $source
            );
        }

        $emailAwl->lastSeen = new DateTimeImmutable('now');

        $emailAwl = $emailAutoWhiteListRepository->save($emailAwl);

        $params = ['name' => $emailAwl->getName(), 'domain' => $emailAwl->getDomain(), 'source' => $emailAwl->getSource()];
        return new JsonResponse($params);
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('EMAIL_AUTOWHITE_DELETE')]
    public function delete(
        Request                      $request,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $name = $data['dynamicId']['name'];
        $domain = $data['dynamicId']['domain'];
        $source = $data['dynamicId']['source'];

        $emailAwl = $emailAutoWhiteListRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$emailAwl) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $name . ', Domain ' . $domain . ' and Source ' . $source
            );
        }
        $emailAutoWhiteListRepository->delete($emailAwl);
        return new JsonResponse('Domain deleted successfully!');
    }
}
