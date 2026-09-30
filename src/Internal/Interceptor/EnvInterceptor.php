<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Testing\AppContext;
use Spiral\Testing\Attribute\Env;
use Spiral\Testing\Stage;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * One instance serves all {@see Env} attributes of a test: Testo keeps the first interceptor of a class.
 *
 * @internal
 */
#[InterceptorOptions(order: Stage::CONFIGURE, testType: TestType::Test)]
final readonly class EnvInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = AppContext::fromTest($info, Env::class);
        /** @var list<Env> $attributes */
        $attributes = $info->getAttribute(Env::class, []);
        foreach ($attributes as $env) {
            $context->addEnv([$env->key => $env->value]);
        }

        return $next($info);
    }
}
