<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Spiral\Testing\Internal\Interceptor\BootCallbacksInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Runs a callback on the container of the application under test before it boots; its arguments are
 * autowired.
 *
 * The callback is the name of a method of the test class (of any visibility), a callable string
 * or array, or a closure where PHP allows one in an attribute.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(BootCallbacksInterceptor::class)]
final class BeforeInit implements Interceptable
{
    public function __construct(
        public readonly string|array|\Closure $callback,
    ) {}
}
