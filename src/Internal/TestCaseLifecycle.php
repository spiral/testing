<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Spiral\Testing\Internal\Interceptor\AppInterceptor;
use Spiral\Testing\Internal\Interceptor\BootInterceptor;
use Testo\Bridge\Mockery\Internal\MockeryInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Brings up the application under test for every test of the class it is put on.
 *
 * @internal
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
#[FallbackInterceptor(AppInterceptor::class)]
#[FallbackInterceptor(BootInterceptor::class)]
#[FallbackInterceptor(MockeryInterceptor::class)]
final readonly class TestCaseLifecycle implements Interceptable {}
