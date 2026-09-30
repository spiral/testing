<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;
use Spiral\Testing\Internal\TestCaseLifecycle;
use Testo\Common\Attribute\AssertMethod;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;

/**
 * Configures the application each test runs against; the Testo interceptors of {@see Stage} bring it up.
 */
#[TestCaseLifecycle]
abstract class TestCase
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

    /** @var array<non-empty-string, mixed> */
    public const ENV = [];

    /** Boot the application before each test; with `false` the test boots it with {@see self::initApp()}. */
    public const MAKE_APP_ON_STARTUP = true;

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

    # Public void methods would be discovered as tests under a class-level #[Test];
    # #[AssertMethod] keeps them out of discovery.
    #[AssertMethod]
    public function beforeBooting(\Closure $callback): void
    {
        AppContext::of($this)->beforeBooting($callback);
    }

    #[AssertMethod]
    public function beforeInit(\Closure $callback): void
    {
        AppContext::of($this)->beforeInit($callback);
    }

    public function getApp(): TestableKernelInterface
    {
        return AppContext::of($this)->getApp();
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
        $app = AppContext::of($this)->boot($env, $container);
        \assert($app instanceof AbstractKernel);

        return $app;
    }

    /**
     * @param array<non-empty-string,mixed> $env
     */
    #[AssertMethod]
    public function initApp(array $env = [], Container $container = new Container()): void
    {
        AppContext::of($this)->boot($env, $container);
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

    /**
     * @deprecated Declare a {@see BeforeTest} method instead; an override is reported when the test runs.
     */
    #[BeforeTest]
    protected function setUp(): void {}

    /**
     * @deprecated Declare an {@see AfterTest} method instead; an override is reported when the test runs.
     */
    #[AfterTest]
    protected function tearDown(): void {}
}
