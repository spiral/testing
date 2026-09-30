<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Testing\AppContext;
use Spiral\Testing\Attribute\BeforeBooting;
use Spiral\Testing\Attribute\BeforeInit;
use Spiral\Testing\Stage;
use Spiral\Testing\TestCase;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * One instance serves all {@see BeforeInit} and {@see BeforeBooting} attributes of a test: Testo keeps
 * the first interceptor of a class.
 *
 * @internal
 */
#[InterceptorOptions(
    # After ConfigInterceptor, so these callbacks see the configs patched by #[Config].
    order: Stage::CONFIGURE + 100,
    testType: TestType::Test,
)]
final readonly class BootCallbacksInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = AppContext::fromTest(
            $info,
            $info->getAttribute(BeforeInit::class) === null ? BeforeBooting::class : BeforeInit::class,
        );

        /** @var list<BeforeInit> $init */
        $init = $info->getAttribute(BeforeInit::class, []);
        foreach ($init as $attribute) {
            $context->beforeInit(self::resolve($attribute->callback, $context->testCase));
        }

        /** @var list<BeforeBooting> $booting */
        $booting = $info->getAttribute(BeforeBooting::class, []);
        foreach ($booting as $attribute) {
            $context->beforeBooting(self::resolve($attribute->callback, $context->testCase));
        }

        return $next($info);
    }

    /**
     * A string naming a method of the test class resolves to that method, whatever its visibility.
     */
    private static function resolve(string|array|\Closure $callback, TestCase $testCase): \Closure
    {
        if (\is_string($callback) && \method_exists($testCase, $callback)) {
            return (new \ReflectionMethod($testCase, $callback))->getClosure($testCase);
        }

        \is_callable($callback) or throw new \LogicException(\sprintf(
            'The boot callback of %s is neither a method of the test class nor a callable.',
            $testCase::class,
        ));

        return $callback(...);
    }
}
