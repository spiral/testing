<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Testing\AppContext;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Internal\ScopeRunner;
use Spiral\Testing\Stage;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * Runs the rest of the pipeline, lifecycle hooks included, inside the declared container scopes.
 *
 * @internal
 */
#[InterceptorOptions(order: Stage::SCOPE, testType: TestType::Test)]
final readonly class TestScopeInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = AppContext::fromTest($info, TestScope::class);

        # Class attributes come first: the one on the method wins.
        /** @var non-empty-list<TestScope> $attributes */
        $attributes = $info->getAttribute(TestScope::class, []);
        $scope = \end($attributes);
        /** @var list<string|\BackedEnum|null> $scopes */
        $scopes = \is_array($scope->scope) ? $scope->scope : [$scope->scope];

        $result = ScopeRunner::run(
            $scopes,
            static fn(): TestResult => $next($info),
            $context->getContainer(),
            $scope->bindings,
        );
        \assert($result instanceof TestResult);

        return $result;
    }
}
