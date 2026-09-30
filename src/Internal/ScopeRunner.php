<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Spiral\Core\Container;
use Spiral\Core\Internal\Introspector;
use Spiral\Core\Scope;

/**
 * @internal
 */
final class ScopeRunner
{
    /**
     * Runs the callback inside the given scopes, skipping the ones the container is already in;
     * the bindings go to the innermost scope.
     *
     * @param list<string|\BackedEnum|null> $scopes Outermost first.
     * @param array<non-empty-string, callable|non-empty-string|object|list{class-string, non-empty-string}> $bindings
     */
    public static function run(array $scopes, \Closure $callback, Container $container, array $bindings = []): mixed
    {
        begin:
        if ($scopes === []) {
            foreach ($bindings as $key => $value) {
                $container->removeBinding($key);
                $container->bind($key, $value);
            }

            return $container->invoke($callback);
        }

        $scope = \array_shift($scopes);
        if ($scope !== null && \in_array($scope, Introspector::scopeNames($container), true)) {
            goto begin;
        }

        $isLast = $scopes === [];
        return $container->runScope(
            new Scope($scope, $isLast ? $bindings : []),
            $isLast
                ? $callback
                : static fn(Container $container): mixed => self::run($scopes, $callback, $container, $bindings),
        );
    }
}
