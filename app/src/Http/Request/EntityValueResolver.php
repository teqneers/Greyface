<?php

namespace App\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EntityValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $param = $argument->getName();
        $value = $request->attributes->get($param);
        if (!$value) {
            if ($argument->isNullable() || !$argument->hasDefaultValue()) {
                return [null];
            }
            return [$argument->getDefaultValue()];
        }

        /** @var MapEntityOptions[] $options */
        $options = $argument->getAttributes(MapEntityOptions::class);
        $options = $options[0]?->options ?? [];

        $object = $this->loadObject($value, $request, $argument, $options);
        if (!$object) {
            if ($argument->isNullable()) {
                return [null];
            }
            if ($argument->hasDefaultValue()) {
                return [$argument->getDefaultValue()];
            }
            throw new NotFoundHttpException(
                sprintf(
                    'Item of class %s not found for parameter %s with value %s.',
                    $argument->getType(),
                    $param,
                    $value
                )
            );
        }
        return [$object];
    }

    abstract protected function loadObject(mixed $value, Request $request, ArgumentMetadata $argument, array $options): ?object;
}
