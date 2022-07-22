<?php

namespace App\Controller\Api\OptOut;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Messenger\Validation;
use IteratorAggregate;
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

        $count = is_array($emails) ? count($emails) : $emails->count();

        if ($emails instanceof IteratorAggregate) {
            $emails = $emails->getIterator();
        }

        return new JsonResponse([
            'results' => $emails,
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
        $optinEmail = OptOutEmail::create($data['email'] ?? '');
        $errors = $validator->validate($optinEmail);
        if (count($errors) > 0) {
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
        $body = $request->getContent();
        $data = json_decode($body, true);

        $optOutEmail->email = $data['email'] ?? '';
        $errors = $validator->validate($optOutEmail);

        if (count($errors) > 0) {
            return Validation::getViolations($errors);
        }
        $email = $optOutEmailRepository->save($optOutEmail);

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
        $optOutEmailRepository->delete($optOutEmail);
        return new JsonResponse('Email deleted successfully!');
    }
}
