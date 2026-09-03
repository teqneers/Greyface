<?php

namespace App\Tests\Messenger;

use App\Messenger\Validation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * This is the 422 body the React frontend parses to attach messages to form
 * fields. Nothing else in the suite asserted its shape, so a change here would
 * silently break every form in the application.
 */
class ValidationTest extends TestCase
{
    private static function violation(string $propertyPath, string $message): ConstraintViolation
    {
        return new ConstraintViolation($message, $message, [], null, $propertyPath, null);
    }

    public function testRespondsWithUnprocessableEntity(): void
    {
        $response = Validation::getViolations(new ConstraintViolationList([
            self::violation('username', 'This value is already used.'),
        ]));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testMapsEachViolationToItsPropertyPath(): void
    {
        $response = Validation::getViolations(new ConstraintViolationList([
            self::violation('username', 'This value is already used.'),
            self::violation('email', 'This is not a valid email address.'),
        ]));

        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            [
                'username' => 'This value is already used.',
                'email' => 'This is not a valid email address.',
            ],
            $body['errors']
        );
    }

    public function testSummarisesEveryMessageInTheErrorString(): void
    {
        $response = Validation::getViolations(new ConstraintViolationList([
            self::violation('username', 'This value is already used.'),
            self::violation('email', 'This is not a valid email address.'),
        ]));

        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            'Validation failed. (This value is already used., This is not a valid email address.)',
            $body['error']
        );
    }

    /**
     * Class-level constraints carry an empty property path. The frontend keys on
     * it, so it must still be present rather than dropped.
     */
    public function testKeepsViolationsWithAnEmptyPropertyPath(): void
    {
        $response = Validation::getViolations(new ConstraintViolationList([
            self::violation('', 'This entry already exists.'),
        ]));

        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('', $body['errors']);
        self::assertSame('This entry already exists.', $body['errors']['']);
    }

    /**
     * Two violations on one field collapse to the last one — the errors map is
     * keyed by property path. Pinned because it is easy to change by accident.
     */
    public function testLaterViolationsOnTheSameFieldWin(): void
    {
        $response = Validation::getViolations(new ConstraintViolationList([
            self::violation('username', 'Too short.'),
            self::violation('username', 'Already used.'),
        ]));

        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['username' => 'Already used.'], $body['errors']);
        self::assertSame('Validation failed. (Too short., Already used.)', $body['error']);
    }

    public function testHandlesAnEmptyViolationList(): void
    {
        $response = Validation::getViolations(new ConstraintViolationList([]));
        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame([], $body['errors']);
        self::assertSame('Validation failed. ()', $body['error']);
    }
}
