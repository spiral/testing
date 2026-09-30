<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Testo\Common\Messenger;
use Testo\Core\Context\TestInfo;
use Testo\Core\Log\Level;

/**
 * The application under test for a single run of a {@see TestCase} test.
 *
 * Travels through the Testo pipeline as `$info->getAttribute(AppContext::class)`. Interceptors before
 * {@see Stage::BOOT} add env and callbacks; later ones reach the booted application.
 *
 * @api
 */
final class AppContext
{
    /** @var \WeakMap<TestCase, self>|null */
    private static ?\WeakMap $contexts = null;

    /** @var array<non-empty-string, true> */
    private static array $reported = [];

    /** @var array<string, mixed> */
    private array $env;

    /** @var list<\Closure> */
    private array $beforeInit = [];

    /** @var list<\Closure> */
    private array $beforeBooting = [];

    /** @var list<\Closure> */
    private array $afterBoot = [];

    private ?TestableKernelInterface $app = null;

    private function __construct(
        public readonly TestCase $testCase,
        private readonly ?Messenger $messenger,
    ) {
        /** @var array<string, mixed> $env */
        $env = $testCase::ENV;
        $this->env = $env;
    }

    /**
     * Binds a new context to the test instance, replacing any previous one.
     */
    public static function attach(TestCase $testCase, ?Messenger $messenger = null): self
    {
        /** @var \WeakMap<TestCase, self> $contexts */
        $contexts = self::$contexts ?? new \WeakMap();
        $contexts[$testCase] = $context = new self($testCase, $messenger);
        self::$contexts = $contexts;

        return $context;
    }

    /**
     * The context of the test instance; one is attached on first use when the instance runs
     * outside the Testo pipeline.
     */
    public static function of(TestCase $testCase): self
    {
        $context = self::$contexts?->offsetExists($testCase) ? self::$contexts[$testCase] : null;

        return $context ?? self::attach($testCase);
    }

    /**
     * The context of the running test, for interceptors reacting to the given attribute.
     *
     * @param class-string $attribute
     *
     * @throws \LogicException when the test is not a {@see TestCase}.
     */
    public static function fromTest(TestInfo $info, string $attribute): self
    {
        $context = $info->getAttribute(self::class);

        return $context instanceof self ? $context : throw new \LogicException(\sprintf(
            '#[%s] on %s requires the test to extend %s.',
            $attribute,
            $info->identity->fqn(),
            TestCase::class,
        ));
    }

    /**
     * @param array<string, mixed> $env Overrides values collected so far.
     */
    public function addEnv(array $env): void
    {
        $this->assertNotBooted('Env');
        $this->env = [...$this->env, ...$env];
    }

    /**
     * Runs the callback on the container before the application boots; arguments are autowired.
     */
    public function beforeInit(callable $callback): void
    {
        $this->assertNotBooted('A beforeInit callback');
        $this->beforeInit[] = $callback(...);
    }

    /**
     * Registers the callback as a booting callback of the kernel; arguments are autowired.
     */
    public function beforeBooting(callable $callback): void
    {
        $this->assertNotBooted('A beforeBooting callback');
        $this->beforeBooting[] = $callback(...);
    }

    /**
     * Runs the callback on the container once the application has booted; arguments are autowired.
     */
    public function afterBoot(callable $callback): void
    {
        $this->assertNotBooted('An afterBoot callback');
        $this->afterBoot[] = $callback(...);
    }

    public function isBooted(): bool
    {
        return $this->app !== null;
    }

    /**
     * Boots the application, replacing one booted before.
     *
     * @param array<string, mixed> $env Overrides the collected env.
     */
    public function boot(array $env = [], Container $container = new Container()): TestableKernelInterface
    {
        $environment = new Environment([...$this->env, ...$env]);

        // Published before the app boots: a `beforeInit` or `beforeBooting` callback that reaches for
        // `getApp()` or `getContainer()` would otherwise find no app and start building a second one.
        $this->app = $app = $this->testCase->createAppInstance($container);
        \assert($app instanceof AbstractKernel);
        $app->getContainer()->removeBinding(EnvironmentInterface::class);
        $app->getContainer()->bindSingleton(EnvironmentInterface::class, $environment);

        foreach ($this->beforeInit as $callback) {
            $app->getContainer()->invoke($callback);
        }

        $app->booting(...$this->beforeBooting);
        $app->run($environment);

        foreach ($this->afterBoot as $callback) {
            $app->getContainer()->invoke($callback);
        }

        self::setScopeContainer($app->getContainer());

        return $app;
    }

    /**
     * The booted application; boots it on first use.
     */
    public function getApp(): TestableKernelInterface
    {
        return $this->app ?? $this->boot();
    }

    /**
     * The container of the innermost scope entered, the root one outside scopes.
     */
    public function getContainer(): Container
    {
        return $this->getApp()->getContainer();
    }

    public function shutdown(): void
    {
        $this->app = null;
        self::setScopeContainer(null);
    }

    /**
     * Reports a deprecation to the Testo stderr channel, once per message.
     *
     * @param non-empty-string $message
     */
    public function deprecated(string $message): void
    {
        if (isset(self::$reported[$message])) {
            return;
        }

        self::$reported[$message] = true;
        $this->messenger === null
            ? \trigger_error($message, \E_USER_DEPRECATED)
            : $this->messenger->log(Messenger::CHANNEL_STDERR, $message, Level::Warning);
    }

    private static function setScopeContainer(?Container $container): void
    {
        (new \ReflectionClass(ContainerScope::class))->setStaticPropertyValue('container', $container);
    }

    private function assertNotBooted(string $what): void
    {
        $this->app === null or throw new \LogicException(
            "$what has no effect once the application has booted. Declare it with an attribute, or boot the app yourself with `MAKE_APP_ON_STARTUP = false`.",
        );
    }
}
