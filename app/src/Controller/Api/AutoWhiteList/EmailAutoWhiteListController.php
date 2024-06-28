<?php

namespace App\Controller\Api\AutoWhiteList;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Messenger\Validation;
use DateTimeImmutable;
use IteratorAggregate;
use OutOfBoundsException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
        /* Because of the Error of Greytool we have to delete the entries with the
         sender_domain value -undef- manually.*/
        $toDelete = $emailAutoWhiteListRepository->findOneBy([
            'domain' => '-undef-'
        ]);
        if ($toDelete) {
            $emailAutoWhiteListRepository->delete($toDelete);
        }

        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $emails = $emailAutoWhiteListRepository->findAll($query, $start, $max, $sortBy, boolval($desc));

        $count = is_array($emails) ? count($emails) : $emails->count();

        if ($emails instanceof IteratorAggregate) {
            $emails = (array)$emails->getIterator();
        }

        return new JsonResponse([
            'results' => $count === 0 ? [] : $emails,
            'count' => $count,
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
            $data['source'] ?? '',
            isset($data['first_seen']) ? new DateTimeImmutable($data['first_seen']) : null,
            isset($data['last_seen']) ? new DateTimeImmutable($data['last_seen']) : null);
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

        $name = $data['dynamicID']['name'];
        $domain = $data['dynamicID']['domain'];
        $source = $data['dynamicID']['source'];

        $dataToUpdate = $data;
        unset($dataToUpdate['dynamicID']);

        if ($data['dynamicID'] === $dataToUpdate) { // if old data and new data is same

            $params = ['name' => $name, 'domain' => $domain, 'source' => $source];

        } else {

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
        }
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

        $name = $data['dynamicID']['name'];
        $domain = $data['dynamicID']['domain'];
        $source = $data['dynamicID']['source'];

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

        $name = $data['name'];
        $domain = $data['domain'];
        $source = $data['source'];

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
