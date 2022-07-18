<?php

namespace App\Command;

use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use App\Messenger\Validation;

/**
 * Trait CommandDispatchingCommand
 *
 */
trait CommandDispatchingCommand
{
    /**
     * @param object              $command
     * @param MessageBusInterface $commandBus
     * @return string[]|null
     */
    private function dispatchCommand(object $command, MessageBusInterface $commandBus): ?array
    {
        try {
            $commandBus->dispatch($command);
            return null;
        } catch (ValidationFailedException $e) {
            return array_merge(['Validation failed.'], Validation::getViolations($e));
        } catch (Throwable $e) {
            return [$e->getMessage()];
        }
    }
}
