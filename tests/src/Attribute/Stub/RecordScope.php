<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute\Stub;

use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Hands the scopes the container is in at {@see \Spiral\Testing\Stage::SCOPED} to the test.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
#[FallbackInterceptor(RecordScopeInterceptor::class)]
final class RecordScope implements Interceptable {}
