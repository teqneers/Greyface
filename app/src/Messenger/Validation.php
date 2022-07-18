<?php


namespace App\Messenger;

use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Class Validation
 *
 */
final class Validation
{
    /**
     */
    private function __construct()
    {
    }

    /**
     * @param ValidationFailedException $e
     * @return string[]
     */
    public static function getViolations(ValidationFailedException $e): array
    {
        return array_map(
            static function (ConstraintViolationInterface $violation) {
                return $violation->getPropertyPath() . ': ' . $violation->getMessage();
            },
            iterator_to_array($e->getViolations())
        );
    }

}
