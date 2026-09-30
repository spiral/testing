<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Spiral\Testing\Internal\Interceptor\TestScopeInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Runs the test, its lifecycle hooks and the interceptors from {@see \Spiral\Testing\Stage::SCOPED} on
 * inside the given container scopes; on a method it replaces the one on the class.
 */
#[\Attribute(flags: \Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
#[FallbackInterceptor(TestScopeInterceptor::class)]
final class TestScope implements Interceptable
{
    /**
     * @param string|\BackedEnum|list<string|\BackedEnum> $scope Outermost first.
     * @param array<non-empty-string, callable|non-empty-string|object|list{class-string, non-empty-string}> $bindings Bound in the innermost scope.
     */
    public function __construct(
        public readonly string|\BackedEnum|array $scope,
        public readonly array $bindings = [],
    ) {}
}
