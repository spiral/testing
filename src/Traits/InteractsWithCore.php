<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Testing\Attribute;
use Testo\Assert;
use Testo\Common\Attribute\AssertMethod;

trait InteractsWithCore
{
    /**
     * @param class-string<BootloaderInterface> $class
     */
    #[AssertMethod]
    public function assertBootloaderRegistered(string $class): void
    {
        Assert::contains(
            $this->getRegisteredBootloaders(),
            $class,
            \sprintf('Bootloader [%s] was not boot.', $class),
        );
    }

    /**
     * @param class-string<BootloaderInterface> $class
     */
    #[AssertMethod]
    public function assertBootloaderMissed(string $class): void
    {
        Assert::iterable($this->getRegisteredBootloaders())->notContains(
            $class,
            \sprintf('Bootloader [%s] was boot.', $class),
        );
    }

    #[AssertMethod]
    public function assertContainerMissed(string $alias): void
    {
        Assert::false(
            $this->getContainer()->has($alias),
            \sprintf('Container contains entry with name [%s].', $alias),
        );
    }

    #[AssertMethod]
    public function assertContainerInstantiable(
        string $alias,
        ?string $class = null,
        array $params = [],
        ?\Closure $callback = null,
    ): void {
        $class ??= $alias;

        if ($params === []) {
            $realObject = $this->getContainer()->get($alias);
        } else {
            $realObject = $this->getContainer()->make($alias, $params);
        }

        Assert::instanceOf(
            $realObject,
            $class,
            \sprintf(
                "Container [%s] was found, but binding [%s] does not match [%s].",
                $alias,
                $class,
                get_class($realObject),
            ),
        );

        if ($callback) {
            $callback($realObject);
        }
    }

    #[AssertMethod]
    public function assertContainerBound(
        string $alias,
        ?string $class = null,
        array $params = [],
        ?\Closure $callback = null,
    ): void {
        Assert::true(
            $this->getContainer()->hasBinding($alias),
            \sprintf('Container does not contain entry with name [%s].', $alias),
        );

        $this->assertContainerInstantiable($alias, $class, $params, $callback);
    }

    #[AssertMethod]
    public function assertContainerBoundNotAsSingleton(
        string $alias,
        string $class,
        array $params = [],
    ): void {
        $this->assertContainerBound($alias, $class, $params);

        Assert::notSame(
            $this->getContainer()->make($alias, $params),
            $this->getContainer()->make($alias, $params),
            \sprintf("Container [%s] is bound, but it contains a singleton.", $alias),
        );
    }

    #[AssertMethod]
    public function assertContainerBoundAsSingleton(string $alias, string $class, ?\Closure $callback = null): void
    {
        $this->assertContainerBound($alias, $class, [], function (object $realObject) use ($alias, $callback): void {
            Assert::same(
                $this->getContainer()->get($alias),
                $realObject,
                \sprintf("Container [%s] is bound, but it contains not a singleton.", $alias),
            );

            if ($callback) {
                $callback->__invoke($realObject);
            }
        });
    }

    /**
     * @return class-string<BootloaderInterface>
     */
    public function getRegisteredBootloaders(): array
    {
        return $this->getApp()->getRegisteredBootloaders();
    }

    /**
     * @param class-string $alias
     * @param class-string|null $interface
     */
    public function mockContainer(string $alias, ?string $interface = null): \Mockery\MockInterface
    {
        $this->getContainer()->removeBinding($alias);

        $this->getContainer()->bindSingleton(
            $alias,
            $mock = \Mockery::mock($interface ?? $alias),
        );

        return $mock;
    }

    /**
     * @param array<non-empty-string, string> $env
     * @return \Spiral\Testing\TestCase|InteractsWithCore
     * @throws \Throwable
     */
    public function withEnvironment(array $env): self
    {
        $current = $this->getContainer()->get(EnvironmentInterface::class)->getAll();

        $this->environment = new Environment(
            array_merge($current, $env),
        );

        $this->getContainer()->removeBinding(EnvironmentInterface::class);
        $this->getContainer()->bind(EnvironmentInterface::class, $this->environment);

        return $this;
    }

    #[AssertMethod]
    public function assertEnvironmentValueSame(string $key, mixed $value): void
    {
        $currentValue = $this->getContainer()->get(EnvironmentInterface::class)->get($key);

        Assert::same(
            $currentValue,
            $value,
            \sprintf('Current environment value for key [%s] is [%s], expected [%s].', $key, $currentValue, $value),
        );
    }

    #[AssertMethod]
    public function assertEnvironmentHasKey(string $key): void
    {
        Assert::true(
            \array_key_exists($key, $this->getContainer()->get(EnvironmentInterface::class)->getAll()),
            \sprintf('Environment does not have key with name [%s].', $key),
        );
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    private function getEnvVariablesFromConfig(): array
    {
        $variables = [];

        foreach ($this->getTestAttributes(Attribute\Env::class) as $attribute) {
            \assert($attribute instanceof Attribute\Env);

            $variables[$attribute->key] = $attribute->value;
        }

        return $variables;
    }
}
