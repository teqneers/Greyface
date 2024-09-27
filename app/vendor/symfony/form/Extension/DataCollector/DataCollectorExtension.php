<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\DataCollector;

use Symfony\Component\Form\AbstractExtension;

/**
 * Extension for collecting data of the forms on a page.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DataCollectorExtension extends AbstractExtension
{
    public function __construct(
        private FormDataCollectorInterface $dataCollector,
    ) {
    }

    protected function loadTypeExtensions(): array
    {
        return [
            new Type\DataCollectorTypeExtension($this->dataCollector),
        ];
    }
}
