<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute\Stub;

/**
 * A test that {@see RecordScopeInterceptor} tells which scopes it found the container in.
 */
interface RecordsScope
{
    /**
     * @param list<string> $scopes Innermost first.
     */
    public function recordInterceptorScopes(array $scopes): void;
}
