<?php

namespace App\Controller\Api\OptOut;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Messenger\Validation;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $emails = $optOutEmailRepository->findAll($start, $max);
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
    #[IsGranted('OPTOUT_EMAIL_CREATE')]
    public function create(
        Request               $request,
        ValidatorInterface    $validator,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $optinEmail = OptOutEmail::create($data['email'] ?? '');
        $errors = $validator->validate($optinEmail);
        if (count($errors) > 0) {
            dump($errors);
            return Validation::getViolations($errors);
        }

        $optinEmail = $optOutEmailRepository->save($optinEmail);
        $params = ['email' => $optinEmail->getEmail()];
        return new JsonResponse($params, Response::HTTP_CREATED);
    }

    #[Route('/{optOutEmail}', methods: ['PUT'])]
    #[IsGranted('OPTOUT_EMAIL_EDIT', subject: 'optOutEmail')]
    #[ParamConverter('optOutEmail', class: OptOutEmail::class, converter: 'app.optOutEmail')]
    public function edit(
        OptOutEmail           $optOutEmail,
        Request               $request,
        ValidatorInterface    $validator,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $email = $optOutEmailRepository->findById($optOutEmail->getEmail());
        if (!$email) {
            throw new OutOfBoundsException('No OptOut Email found for id ' . $optOutEmail->getEmail());
        }
        $body = $request->getContent();
        $data = json_decode($body, true);

        $email->email = $data['email'] ?? '';
        $errors = $validator->validate($email);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $email = $optOutEmailRepository->save($email);

        $params = ['email' => $email->getEmail()];
        return new JsonResponse($params);
    }

    #[Route('/{optOutEmail}', methods: ['DELETE'])]
    #[IsGranted('OPTOUT_EMAIL_DELETE', subject: 'optOutEmail')]
    #[ParamConverter('optOutEmail', class: OptOutEmail::class, converter: 'app.optOutEmail')]
    public function delete(
        OptOutEmail           $optOutEmail,
        OptOutEmailRepository $optOutEmailRepository
    ): Response
    {
        $email = $optOutEmailRepository->findById($optOutEmail->getEmail());
        if (!$email) {
            throw new OutOfBoundsException('No OptOut Email found for id ' . $optOutEmail->getEmail());
        }
        $optOutEmailRepository->delete($email);
        return new JsonResponse('Email deleted successfully!');
    }
}
