<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\Patch\Set;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Internal\Introspector;
use Spiral\Core\Scope;
use Spiral\Testing\Attribute\TestScope;

abstract class TestCase extends BaseTestCase
{
    use Traits\InteractsWithConsole;
    use Traits\InteractsWithHttp;
    use Traits\InteractsWithCore;
    use Traits\InteractsWithFileSystem;
    use Traits\InteractsWithConfig;
    use Traits\InteractsWithDispatcher;
    use Traits\InteractsWithMailer;
    use Traits\InteractsWithQueue;
    use Traits\InteractsWithEvents;
    use Traits\InteractsWithStorage;
    use Traits\InteractsWithExceptions;
    use Traits\InteractsWithViews;
    use Traits\InteractsWithTranslator;
    use Traits\InteractsWithScaffolder;
    use MockeryPHPUnitIntegration;

    public const ENV = [];
    public const MAKE_APP_ON_STARTUP = true;

    private ?TestableKernelInterface $app = null;

    /** @var array<\Closure> */
    private array $beforeBooting = [];

    /** @var array<\Closure> */
    private array $beforeInit = [];

    private ?EnvironmentInterface $environment = null;

    /**
     * @return array<class-string>|array<class-string, array<non-empty-string, mixed>>
     */
    public function defineBootloaders(): array
    {
        return [];
    }

    /**
     * @return array{
     *     app: string,
     *     public: string,
     *     vendor: string,
     *     runtime: string,
     *     cache: string,
     *     config: string,
     *     resources: string,
     * }|array<non-empty-string, string>
     */
    public function defineDirectories(string $root): array
    {
        return [
            'root' => $root,
            'app' => $root . '/app',
            'runtime' => $root . '/runtime',
            'cache' => $root . '/runtime/cache',
        ];
    }

    public function rootDirectory(): string
    {
        return dirname(__DIR__);
    }

    public function beforeBooting(\Closure $callback): void
    {
        $this->beforeBooting[] = $callback;
    }

    public function beforeInit(\Closure $callback): void
    {
        $this->beforeInit[] = $callback;
    }

    public function getApp(): TestableKernelInterface
    {
        if (!$this->app) {
            $this->initApp();
        }
        return $this->app;
    }

    public function getContainer(): Container
    {
        return $this->getApp()->getContainer();
    }

    public function createAppInstance(Container $container = new Container()): TestableKernelInterface
    {
        return TestApp::create(
            directories: $this->defineDirectories(
                $this->rootDirectory(),
            ),
            handleErrors: false,
            container: $container,
        )->withBootloaders($this->defineBootloaders());
    }

    /**
     * @param array<non-empty-string,mixed> $env
     * @return AbstractKernel|TestableKernelInterface
     */
    public function makeApp(array $env = [], Container $container = new Container()): AbstractKernel
    {
        $environment = new Environment($env);

        $app = $this->createAppInstance($container);
        $app->getContainer()->removeBinding(EnvironmentInterface::class);
        $app->getContainer()->bindSingleton(EnvironmentInterface::class, $environment);

        foreach ($this->beforeInit as $callback) {
            $app->getContainer()->invoke($callback);
        }

        $configs = $this->getTestAttributes(Attribute\Config::class);
        $app->booting(static function (ConfigsInterface $configManager) use ($configs) {
            foreach ($configs as $attribute) {
                \assert($attribute instanceof Attribute\Config);
                [$config, $key] = explode('.', $attribute->path, 2);

                $configManager->modify(
                    $config,
                    new Set($key, $attribute->closure?->__invoke() ?? $attribute->value),
                );
            }
        });

        $app->booting(...$this->beforeBooting);
        $app->run($environment);

        return $app;
    }

    public function initApp(array $env = [], Container $container = new Container()): void
    {
        $this->app = $this->makeApp($env, $container);
        $this->suppressExceptionHandlingIfAttributeDefined();

        (new \ReflectionClass(ContainerScope::class))
            ->setStaticPropertyValue('container', $this->app->getContainer());
    }

    /**
     * @param array<string, string|array|callable|object> $bindings
     * @throws \Throwable
     */
    public function runScoped(\Closure $callback, array $bindings = [], ?string $name = null): mixed
    {
        if ($this->environment) {
            $bindings[EnvironmentInterface::class] = $this->environment;
        }

        return $this->getContainer()->runScope($bindings, $callback);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (static::MAKE_APP_ON_STARTUP) {
            $variables = [...static::ENV, ...$this->getEnvVariablesFromConfig()];
            $this->initApp($variables);
        }

        $this->setUpTraits();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownTraits();

        (new \ReflectionClass(ContainerScope::class))
            ->setStaticPropertyValue('container', null);
    }

    /**
     * @template TClass
     *
     * @param class-string<TClass> $attribute
     * @param null|non-empty-string $method Method name
     *
     * @return array<int, TClass>
     */
    protected function getTestAttributes(string $attribute, ?string $method = null): array
    {
        try {
            $methodName = $method ?? (\method_exists($this, 'name') ? $this->name() : $this->getName(false));
            $result = [];
            $attributes = (new \ReflectionMethod($this, $methodName))->getAttributes($attribute);
            foreach ($attributes as $attr) {
                $result[] = $attr->newInstance();
            }
            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function setUpTraits(): void
    {
        $this->runTraitSetUpOrTearDown('setUp');
    }

    protected function tearDownTraits(): void
    {
        $this->runTraitSetUpOrTearDown('tearDown');
    }

    protected function invokeTestMethod(string $methodName, array $testArguments): mixed
    {
        $scope = $this->getTestScope();
        if ($scope === null) {
            return parent::invokeTestMethod($methodName, $testArguments);
        }

        $scopes = \is_array($scope->scope) ? $scope->scope : [$scope->scope];
        $result = self::runScopes($scopes, function () use ($methodName, $testArguments): mixed {
            return parent::invokeTestMethod($methodName, $testArguments);
        }, $this->getContainer(), $scope->bindings);

        return $result;
    }

    private static function runScopes(array $scopes, \Closure $callback, Container $container, array $bindings): mixed
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
                : static function (Container $container) use ($scopes, $callback, $bindings): mixed {
                    return self::runScopes($scopes, $callback, $container, $bindings);
                },
        );
    }

    private function runTraitSetUpOrTearDown(string $method): void
    {
        $ref = new \ReflectionClass(static::class);

        foreach ($ref->getTraits() as $trait) {
            if (\method_exists($this, $name = $method . $trait->getShortName())) {
                $this->{$name}();
            }
        }

        while ($parent = $ref->getParentClass()) {
            foreach ($parent->getTraits() as $trait) {
                if (\method_exists($this, $name = $method . $trait->getShortName())) {
                    $this->{$name}();
                }
            }

            $ref = $parent;
        }
    }

    private function getTestScope(): ?TestScope
    {
        $attribute = $this->getTestAttributes(TestScope::class)[0] ?? null;
        if ($attribute !== null) {
            return $attribute;
        }

        try {
            foreach ((new \ReflectionClass($this))->getAttributes(TestScope::class) as $attr) {
                return $attr->newInstance();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
