<?php

namespace App\Controller\Api\OptIn;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Messenger\Validation;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/opt-in/emails')]
class OptInEmailController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('OPTIN_EMAIL_LIST')]
    public function list(
        Request               $request,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $emails = $optInEmailRepository->findAll($start, $max);
        $data = [];
        foreach ($emails as $email) {
            $data[] = [
                'email' => $email->getEmail(),
            ];
        }
        return new JsonResponse([
            'results' => $data,
            'count' => is_array($emails) ? count($emails) : $emails->count(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('OPTIN_EMAIL_CREATE')]
    public function create(
        Request               $request,
        ValidatorInterface    $validator,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $optinEmail = OptInEmail::create($data['email'] ?? '');
        $errors = $validator->validate($optinEmail);
        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }

        $optinEmail = $optInEmailRepository->save($optinEmail);
        $params = ['email' => $optinEmail->getEmail()];
        return new JsonResponse($params, Response::HTTP_CREATED);
    }

    #[Route('/{optInEmail}', methods: ['PUT'])]
    #[IsGranted('OPTIN_EMAIL_EDIT', subject: 'optInEmail')]
    #[ParamConverter('optInEmail', class: OptInEmail::class, converter: 'app.optInEmail')]
    public function edit(
        OptInEmail           $optInEmail,
        Request               $request,
        ValidatorInterface    $validator,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $email = $optInEmailRepository->findById($optInEmail->getEmail());
        if (!$email) {
            throw new OutOfBoundsException('No OptIn Email found for id ' . $optInEmail->getEmail());
        }
        $body = $request->getContent();
        $data = json_decode($body, true);

        $email->email = $data['email'] ?? '';
        $errors = $validator->validate($email);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $email = $optInEmailRepository->save($email);

        $params = ['email' => $email->getEmail()];
        return new JsonResponse($params);
    }

    #[Route('/{optInEmail}', methods: ['DELETE'])]
    #[IsGranted('OPTIN_EMAIL_DELETE', subject: 'optInEmail')]
    #[ParamConverter('optInEmail', class: OptInEmail::class, converter: 'app.optInEmail')]
    public function delete(
        OptInEmail           $optInEmail,
        OptInEmailRepository $optInEmailRepository
    ): Response
    {
        $email = $optInEmailRepository->findById($optInEmail->getEmail());
        if (!$email) {
            throw new OutOfBoundsException('No OptIn Email found for id ' . $optInEmail->getEmail());
        }
        $optInEmailRepository->delete($email);
        return new JsonResponse('Email deleted successfully!');
    }
}
