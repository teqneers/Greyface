<?php

namespace App\Messenger;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class Validation
{

    private function __construct()
    {
    }

    /**
     * @param ConstraintViolationListInterface $errors
     * @return JsonResponse
     */
    public static function getViolations(ConstraintViolationListInterface $errors): JsonResponse
    {
        $violationMessages = [];
        $formErrors = [];
        foreach ($errors as $error) {
            /** @var ConstraintViolationInterface $error */
            $violationMessages[] = $error->getMessage();
            $formErrors[$error->getPropertyPath()] = $error->getMessage();

        }
        return new JsonResponse([
            "errors" => $formErrors,
            'error' => 'Validation failed.' . ' (' . implode(', ', $violationMessages) . ')'],
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
