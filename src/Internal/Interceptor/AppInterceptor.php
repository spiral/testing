<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Testing\AppContext;
use Spiral\Testing\Internal\Deprecations;
use Spiral\Testing\Internal\TestCaseInstance;
use Spiral\Testing\Stage;
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
 * Runs each test on a fresh {@see TestCase} instance and hands its {@see AppContext} down the pipeline.
 *
 * @internal
 */
#[InterceptorOptions(order: Stage::INSTANCE, testType: TestType::Test)]
final readonly class AppInterceptor implements TestRunInterceptor
{
    public function __construct(
        private Deprecations $deprecations,
    ) {}

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

        $context = AppContext::attach($testCase, $this->deprecations);
        self::reportOverriddenHooks($class, $this->deprecations);

        $info = (new TestInfo(
            name: $info->name,
            caseInfo: $info->caseInfo->withInstance(new TestCaseInstance($testCase)),
            testDefinition: $info->testDefinition,
            arguments: $info->arguments,
            attributes: $info->attributes,
            identity: $info->identity,
        ))->withAttribute(AppContext::class, $context);

        try {
            return $next($info);
        } catch (\Throwable $e) {
            # The boot, a scope or a lifecycle hook failed: without this the pipeline would be aborted
            # instead of the test being reported the way a failing test body is.
            return self::failed($info, $e);
        } finally {
            $context->shutdown();
        }
    }

    private static function reportOverriddenHooks(\ReflectionClass $class, Deprecations $deprecations): void
    {
        foreach (['setUp' => 'BeforeTest', 'tearDown' => 'AfterTest'] as $hook => $attribute) {
            $declaring = $class->getMethod($hook)->getDeclaringClass()->getName();
            $declaring === TestCase::class or $deprecations->report(\sprintf(
                '%s::%s() overrides %s::%s(), which is deprecated: move the code into a #[\Testo\Lifecycle\%s] method.',
                $declaring,
                $hook,
                TestCase::class,
                $hook,
                $attribute,
            ));
        }
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
