<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute\Stub;

use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Records the scopes the container is in when a {@see \Spiral\Testing\Stage::SCOPED} interceptor runs.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
#[FallbackInterceptor(RecordScopeInterceptor::class)]
final class RecordScope implements Interceptable
{
    /** @var list<string> */
    public static array $scopes = [];
}
