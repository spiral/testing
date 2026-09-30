<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Core\Container;
use Spiral\Testing\AppContext;
use Spiral\Testing\Attribute\WithoutExceptionHandling;
use Spiral\Testing\Internal\RethrowingExceptionHandler;
use Spiral\Testing\Stage;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * @internal
 */
#[InterceptorOptions(order: Stage::CONFIGURE, testType: TestType::Test)]
final readonly class ExceptionHandlingInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        AppContext::fromTest($info, WithoutExceptionHandling::class)->afterBoot(
            static fn(Container $container) => RethrowingExceptionHandler::install($container),
        );

        return $next($info);
    }
}
