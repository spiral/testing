<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Testo\Bridge\Mockery\Internal\MockeryInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Drives the {@see \Spiral\Testing\TestCase} lifecycle for every test of the class it is put on.
 *
 * @internal
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
#[FallbackInterceptor(TestCaseInterceptor::class)]
#[FallbackInterceptor(MockeryInterceptor::class)]
final readonly class TestCaseLifecycle implements Interceptable {}
