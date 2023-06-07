<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Env
{
    public function __construct(
        public readonly string $key,
        public readonly int|string|null|bool $value = null,
    ) {
    }
}
