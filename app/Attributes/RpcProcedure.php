<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RpcProcedure
{
    public function __construct(
        public string $version = 'v1',
        public ?string $group = null
    ) {}
}
