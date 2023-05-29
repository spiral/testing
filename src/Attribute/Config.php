<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Attribute;
use Closure;

#[Attribute(flags: Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
final class Config
{
    public ?Closure $closure;

    public function __construct(
        public string $path,
        public mixed $value = null,
        callable $closure = null,
    ) {
        $this->closure = $closure !== null ? $closure(...) : null;
    }
}
