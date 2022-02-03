<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Closure;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Auth\AuthContextInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Files\Files;
use Spiral\Session\SessionInterface;

use function Spiral\Testing\Traits\;

abstract class TestCase extends BaseTestCase
{
    use Traits\ConsoleAssertions,
        Traits\HttpAssertions,
        Traits\KernelAssertions,
        Traits\ApplicationMocks,
        MockeryPHPUnitIntegration;

    public const ENV = [];

    private KernelInterface $app;
    private Container $container;
    /** @var array<Closure> */
    private array $beforeStarting = [];
    private ?AuthContextInterface $auth = null;
    private ?SessionInterface $session = null;

    /**
     * @return array<class-string>|array<class-string, array<non-empty-string, mixed>>
     */
    abstract public function defineBootloaders(): array;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshApp();
    }

    final public function beforeStarting(Closure $callback): void
    {
        $this->beforeStarting[] = $callback;
    }

    final public function getApp(): App
    {
        return $this->app;
    }

    final public function getContainer(): Container
    {
        return $this->container;
    }

    protected function makeApp(array $env = []): App
    {
        $environment = new Environment($env);

        $root = dirname(__DIR__);

        $app = App::createWithBootloaders(
            $this->defineBootloaders(),
            [
                'root' => $root,
                'app' => $root.'/App',
                'runtime' => $root.'/runtime/tests',
                'cache' => $root.'/runtime/tests/cache',
            ],
            false
        );

        $this->container = $app->getContainer();
        $app->getContainer()->bindSingleton(EnvironmentInterface::class, $environment);

        $app->starting(...$this->beforeStarting);
        $app->run($environment);

        return $app;
    }

    protected function refreshApp()
    {
        $this->app = $this->makeApp(static::ENV);
    }

    public function getConfig(string $config): array
    {
        return $this->getContainer()->get(ConfigsInterface::class)->getConfig($config);
    }

    public function setConfig(string $config, $data): void
    {
        $this->getContainer()->get(ConfigsInterface::class)->setDefaults(
            $config,
            $data
        );
    }

    public function cleanupDirectories(string ...$directories)
    {
        $fs = new Files();

        foreach ($directories as $directory) {
            if ($fs->isDirectory($directory)) {
                $fs->deleteDirectory($directory);
            }
        }
    }

    public function runScoped(Closure $callback): mixed
    {
        $scopes = [];

        if ($this->auth) {
            $scopes[AuthContextInterface::class] = $this->auth;
        }

        if ($this->session) {
            $scopes[SessionInterface::class] = $this->session;
        }

        return $this->getContainer()->runScope($scopes, $callback);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->auth = null;
        $this->session = null;

        $runtime = $this->getContainer()->get(DirectoriesInterface::class)->get('runtime');
        $this->cleanupDirectories($runtime);
    }
}
