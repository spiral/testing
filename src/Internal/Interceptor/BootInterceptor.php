<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Testing\AppContext;
use Spiral\Testing\Stage;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * @internal
 */
#[InterceptorOptions(order: Stage::BOOT, testType: TestType::Test)]
final readonly class BootInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = $info->getAttribute(AppContext::class);
        $context instanceof AppContext and $context->testCase::MAKE_APP_ON_STARTUP and $context->boot();

        return $next($info);
    }
}
