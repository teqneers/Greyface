<?php

namespace App\Http\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class EntityParamConverter
 *
 */
abstract class EntityParamConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $value = $request->attributes->get($param);

        if (!$value && $configuration->isOptional()) {
            return false;
        }

        $object = $this->loadObject($value, $request, $configuration);
        if (!$object && !$configuration->isOptional()) {
            throw new NotFoundHttpException(
                sprintf(
                    'Item of class %s not found for parameter %s with value %s.',
                    $configuration->getClass(),
                    $param,
                    $value
                )
            );
        }
        $request->attributes->set($param, $object);

        return true;
    }

    /**
     * @param mixed          $value
     * @param Request        $request
     * @param ParamConverter $configuration
     * @return object|null
     * @throws HttpException
     */
    abstract protected function loadObject($value, Request $request, ParamConverter $configuration): ?object;
}
