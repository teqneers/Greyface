<?php

namespace App\Controller\Api\OptOut;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Messenger\Validation;
use IteratorAggregate;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OutOfBoundsException;

#[Route('/api/opt-out/emails')]
class OptOutEmailController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('OPTOUT_EMAIL_LIST')]
    public function list(
        Request               $request,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $emails = $optOutEmailRepository->findAll($query, $start, $max, $sortBy, boolval($desc));

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
    #[IsGranted('OPTOUT_EMAIL_CREATE')]
    public function create(
        Request               $request,
        ValidatorInterface    $validator,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $data['email'] = is_array($data['email']) ? array_unique($data['email']) : array($data['email']);

        $emails = [];
        foreach ($data['email'] as $email) {
            $emailToCreate = new OptOutEmail($email);
            $errors = $validator->validate($emailToCreate);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $emails[] = $emailToCreate;
        }

        foreach ($emails as $email) {
            $optOutEmailRepository->save($email);
        }

        return new JsonResponse('Data has been added successfully!');
    }

    #[Route('/edit', methods: ['PUT'])]
    #[IsGranted('OPTOUT_EMAIL_EDIT')]
    public function edit(
        Request               $request,
        ValidatorInterface    $validator,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $emailToFind = $data['dynamicID']['email'] ?? '';
        if ($emailToFind === $data['email']) { // if old data and new data is same

            $params = ['email' => $data['email']];

        } else {
            $optOutEmail = $optOutEmailRepository->findById($emailToFind);
            if (!$optOutEmail) {
                throw new OutOfBoundsException('No Opt-out Email found for ' . $emailToFind);
            }

            $optOutEmail->email = $data['email'] ?? '';
            $errors = $validator->validate($optOutEmail);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $email = $optOutEmailRepository->save($optOutEmail);

            $params = ['email' => $email->getEmail()];
        }
        return new JsonResponse($params);
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('OPTOUT_EMAIL_DELETE')]
    public function delete(
        Request           $request,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $email = $data['email'] ?? '';

        $optOutEmail = $optOutEmailRepository->findById($email);
        if (!$optOutEmail) {
            throw new OutOfBoundsException('No Opt-out Email found for ' . $email);
        }
        $optOutEmailRepository->delete($optOutEmail);
        return new JsonResponse('Email deleted successfully!');
    }
}
