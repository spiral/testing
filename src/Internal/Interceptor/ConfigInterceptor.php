<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal\Interceptor;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Set;
use Spiral\Testing\AppContext;
use Spiral\Testing\Attribute\Config;
use Spiral\Testing\Stage;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * One instance serves all {@see Config} attributes of a test: Testo keeps the first interceptor of a class.
 *
 * @internal
 */
#[InterceptorOptions(order: Stage::CONFIGURE, testType: TestType::Test)]
final readonly class ConfigInterceptor implements TestRunInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var list<Config> $configs */
        $configs = $info->getAttribute(Config::class, []);
        AppContext::fromTest($info, Config::class)->beforeBooting(
            static function (ConfiguratorInterface $configManager) use ($configs): void {
                foreach ($configs as $attribute) {
                    [$config, $key] = \explode('.', $attribute->path, 2) + [1 => ''];

                    $configManager->modify(
                        $config,
                        new Set($key, $attribute->closure?->__invoke() ?? $attribute->value),
                    );
                }
            },
        );

        return $next($info);
    }
}
