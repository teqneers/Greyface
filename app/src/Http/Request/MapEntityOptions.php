<?php

namespace App\Http\Request;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class MapEntityOptions
{
    public function __construct(
        public readonly array $options = []
    )
    {
    }
}
