<?php

namespace App\Controller\Api\OptIn;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Messenger\Validation;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OutOfBoundsException;
use IteratorAggregate;

#[Route('/api/opt-in/emails')]
class OptInEmailController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('OPTIN_EMAIL_LIST')]
    public function list(
        Request              $request,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $emails = $optInEmailRepository->findAll($query, $start, $max, $sortBy, boolval($desc));

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
    #[IsGranted('OPTIN_EMAIL_CREATE')]
    public function create(
        Request              $request,
        ValidatorInterface   $validator,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $data['email'] = is_array($data['email']) ? array_unique($data['email']) : array($data['email']);

        $emails = [];
        foreach ($data['email'] as $email) {
            $emailToCreate = new OptInEmail($email);
            $errors = $validator->validate($emailToCreate);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $emails[] = $emailToCreate;
        }

        foreach ($emails as $email) {
            $optInEmailRepository->save($email);
        }

        return new JsonResponse('Data has been added successfully!');
    }

    #[Route('/edit', methods: ['PUT'])]
    #[IsGranted('OPTIN_EMAIL_EDIT')]
    public function edit(
        Request              $request,
        ValidatorInterface   $validator,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $emailToFind = $data['dynamicID']['email'] ?? '';
        if ($emailToFind === $data['email']) { // if old data and new data is same

            $params = ['email' => $data['email']];

        } else {
            $optInEmail = $optInEmailRepository->findById($emailToFind);
            if (!$optInEmail) {
                throw new OutOfBoundsException('No Opt-In Email found for ' . $emailToFind);
            }

            $optInEmail->email = $data['email'] ?? '';
            $errors = $validator->validate($optInEmail);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }
            $email = $optInEmailRepository->save($optInEmail);

            $params = ['email' => $email->getEmail()];
        }
        return new JsonResponse($params);
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('OPTIN_EMAIL_DELETE')]
    public function delete(
        Request              $request,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $email = $data['email'] ?? '';

        $optInEmail = $optInEmailRepository->findById($email);
        if (!$optInEmail) {
            throw new OutOfBoundsException('No Opt-In Email found for ' . $email);
        }
        $optInEmailRepository->delete($optInEmail);
        return new JsonResponse('Record deleted successfully!');
    }
}
