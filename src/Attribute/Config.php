<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Spiral\Testing\Internal\Interceptor\ConfigInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Patches a config value of the application under test while it boots; on a method it is applied
 * after the class.
 */
#[\Attribute(flags: \Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(ConfigInterceptor::class)]
final class Config implements Interceptable
{
    public ?\Closure $closure;

    public function __construct(
        public string $path,
        public mixed $value = null,
        ?callable $closure = null,
    ) {
        $this->closure = $closure !== null ? $closure(...) : null;
    }
}
