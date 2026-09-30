<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Spiral\Testing\Internal\Interceptor\ExceptionHandlingInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Lets exceptions escape to the test instead of going through the application exception handler.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
#[FallbackInterceptor(ExceptionHandlingInterceptor::class)]
final class WithoutExceptionHandling implements Interceptable {}
