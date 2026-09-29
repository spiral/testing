<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Spiral\Testing\TestCase;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Exception\CancelTest;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * Runs each test on a fresh {@see TestCase} instance, with the test body going through
 * {@see TestCase::invokeTestMethod()}.
 *
 * @internal
 */
#[InterceptorOptions(
    # Outside the lifecycle hooks, so a failing setUp() / tearDown() reaches the catch below, and inside
    # the assertion collector, so their assertions count.
    order: InterceptorOptions::ORDER_CLOSE_TO_TEST - 1,
    testType: TestType::Test,
)]
final readonly class TestCaseInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $class = $info->caseInfo->definition->reflection;
        if ($class === null || !$class->isSubclassOf(TestCase::class)) {
            return $next($info);
        }

        # Testo shares one instance across the whole case; a TestCase keeps the booted app and
        # the fakes in its properties, so every test gets its own.
        $testCase = $class->newInstance();
        \assert($testCase instanceof TestCase);

        $invoke = self::prepare($testCase, $info->testDefinition->reflection);

        $handler = $info->caseInfo->handler;
        $info = new TestInfo(
            name: $info->name,
            caseInfo: $info->caseInfo
                ->withInstance(new TestCaseInstance($testCase))
                ->with(handler: static fn(TestInfo $info): mixed => $invoke(
                    static fn(): mixed => $handler($info),
                )),
            testDefinition: $info->testDefinition,
            arguments: $info->arguments,
            attributes: $info->attributes,
            identity: $info->identity,
        );

        try {
            return $next($info);
        } catch (\Throwable $e) {
            # A hook failure would otherwise abort the pipeline; report it the way a failing test body is.
            return self::failed($info, $e);
        }
    }

    /**
     * @return \Closure(\Closure): mixed
     */
    private static function prepare(TestCase $testCase, \ReflectionFunctionAbstract $method): \Closure
    {
        $prepare = \Closure::bind(
            /** @psalm-suppress InaccessibleProperty, InaccessibleMethod */
            static function (TestCase $testCase) use ($method): \Closure {
                $testCase->testMethod = $method;

                return $testCase->invokeTestMethod(...);
            },
            null,
            TestCase::class,
        );
        \assert($prepare !== null);

        /** @var \Closure(\Closure): mixed */
        return $prepare($testCase);
    }

    private static function failed(TestInfo $info, \Throwable $e): TestResult
    {
        return new TestResult(
            info: $info,
            status: match (true) {
                $e instanceof SkipTest => Status::Skipped,
                $e instanceof CancelTest => Status::Cancelled,
                default => Status::Error,
            },
            failure: $e,
            attributes: ['description' => $info->testDefinition->getDescription()],
        );
    }
}
