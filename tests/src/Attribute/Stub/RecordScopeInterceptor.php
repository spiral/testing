<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute\Stub;

use Spiral\Core\Internal\Introspector;
use Spiral\Testing\AppContext;
use Spiral\Testing\Stage;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

#[InterceptorOptions(order: Stage::SCOPED)]
final readonly class RecordScopeInterceptor implements TestRunInterceptor
{
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = AppContext::fromTest($info, RecordScope::class);
        $test = $context->testCase;
        \assert($test instanceof RecordsScope);
        $test->recordInterceptorScopes(Introspector::scopeNames($context->getContainer()));

        return $next($info);
    }
}
