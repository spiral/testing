<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Spiral\Testing\Internal\Interceptor\EnvInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Sets an environment variable of the application under test; on a method it overrides the class.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(EnvInterceptor::class)]
final class Env implements Interceptable
{
    public function __construct(
        public readonly string $key,
        public readonly int|string|null|bool $value = null,
    ) {}
}
