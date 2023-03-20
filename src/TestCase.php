<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Closure;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;

abstract class TestCase extends BaseTestCase
{
    use Traits\InteractsWithConsole,
        Traits\InteractsWithHttp,
        Traits\InteractsWithCore,
        Traits\InteractsWithFileSystem,
        Traits\InteractsWithConfig,
        Traits\InteractsWithDispatcher,
        Traits\InteractsWithMailer,
        Traits\InteractsWithQueue,
        Traits\InteractsWithEvents,
        Traits\InteractsWithStorage,
        Traits\InteractsWithExceptions,
        Traits\InteractsWithViews,
        Traits\InteractsWithTranslator,
        MockeryPHPUnitIntegration;

    public const ENV = [];
    public const MAKE_APP_ON_STARTUP = true;

    private ?TestableKernelInterface $app = null;
    /** @var array<Closure> */
    private array $beforeBooting = [];
    /** @var array<Closure> */
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
            'app' => $root.'/app',
            'runtime' => $root.'/runtime',
            'cache' => $root.'/runtime/cache',
        ];
    }

    public function rootDirectory(): string
    {
        return dirname(__DIR__);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (static::MAKE_APP_ON_STARTUP) {
            $this->initApp(static::ENV);
        }
    }

    final public function beforeBooting(Closure $callback): void
    {
        $this->beforeBooting[] = $callback;
    }

    final public function beforeInit(Closure $callback): void
    {
        $this->beforeInit[] = $callback;
    }

    final public function getApp(): TestApp
    {
        if (!$this->app) {
            $this->initApp();
        }
        return $this->app;
    }

    final public function getContainer(): Container
    {
        return $this->getApp()->getContainer();
    }

    public function createAppInstance(Container $container = new Container()): TestableKernelInterface
    {
        return TestApp::create(
            directories: $this->defineDirectories(
                $this->rootDirectory()
            ),
            handleErrors: false,
            container: $container
        )->withBootloaders($this->defineBootloaders());
    }

    /**
     * @param array<non-empty-string,mixed> $env
     * @return AbstractKernel|TestableKernelInterface
     */
    final public function makeApp(array $env = []): AbstractKernel
    {
        $environment = new Environment($env);

        $app = $this->createAppInstance();
        $app->getContainer()->bindSingleton(EnvironmentInterface::class, $environment);

        foreach ($this->beforeInit as $callback) {
            $app->getContainer()->invoke($callback);
        }

        $app->booting(...$this->beforeBooting);
        $app->run($environment);

        return $app;
    }

    final public function initApp(array $env = []): void
    {
        $this->app = $this->makeApp($env);
    }

    /**
     * @param array<string, string|array|callable|object> $bindings
     * @throws \Throwable
     */
    final public function runScoped(Closure $callback, array $bindings = []): mixed
    {
        if ($this->environment) {
            $bindings[EnvironmentInterface::class] = $this->environment;
        }

        return $this->getContainer()->runScope($bindings, $callback);
    }
}
