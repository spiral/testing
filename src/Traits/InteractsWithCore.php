<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Testing\Attribute;

trait InteractsWithCore
{
    /**
     * @param class-string<BootloaderInterface> $class
     */
    public function assertBootloaderRegistered(string $class): void
    {
        $this->assertContains(
            $class,
            $this->getRegisteredBootloaders(),
            \sprintf('Bootloader [%s] was not boot.', $class)
        );
    }

    /**
     * @param class-string<BootloaderInterface> $class
     */
    public function assertBootloaderMissed(string $class): void
    {
        $this->assertNotContains(
            $class,
            $this->getRegisteredBootloaders(),
            \sprintf('Bootloader [%s] was boot.', $class)
        );
    }

    public function assertContainerMissed(string $alias): void
    {
        $this->assertFalse(
            $this->getContainer()->has($alias),
            \sprintf('Container contains entry with name [%s].', $alias)
        );
    }

    public function assertContainerInstantiable(
        string $alias,
        ?string $class = null,
        array $params = [],
        ?\Closure $callback = null
    ): void {
        $class ??= $alias;

        if ($params === []) {
            $realObject = $this->getContainer()->get($alias);
        } else {
            $realObject = $this->getContainer()->make($alias, $params);
        }

        $this->assertInstanceOf(
            $class,
            $realObject,
            \sprintf(
                "Container [%s] was found, but binding [%s] does not match [%s].",
                $alias,
                $class,
                get_class($realObject)
            )
        );

        if ($callback) {
            $callback($realObject);
        }
    }

    public function assertContainerBound(
        string $alias,
        ?string $class = null,
        array $params = [],
        ?\Closure $callback = null
    ): void {
        $this->assertTrue(
            $this->getContainer()->has($alias),
            \sprintf('Container does not contain entry with name [%s].', $alias)
        );

        $this->assertContainerInstantiable($alias, $class, $params, $callback);
    }

    public function assertContainerBoundNotAsSingleton(
        string $alias,
        string $class,
        array $params = []
    ): void {
        $this->assertContainerBound($alias, $class, $params);

        $this->assertNotSame(
            $this->getContainer()->make($alias, $params),
            $this->getContainer()->make($alias, $params),
            \sprintf("Container [%s] is bound, but it contains a singleton.", $alias)
        );
    }

    public function assertContainerBoundAsSingleton(string $alias, string $class, ?\Closure $callback = null): void
    {
        $this->assertContainerBound($alias, $class, [], function (object $realObject) use ($alias, $callback): void {
            $this->assertSame(
                $realObject,
                $this->getContainer()->get($alias),
                \sprintf("Container [%s] is bound, but it contains not a singleton.", $alias)
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
     * @return \Mockery\MockInterface
     */
    public function mockContainer(string $alias, ?string $interface = null): \Mockery\MockInterface
    {
        $this->getContainer()->removeBinding($alias);

        $this->getContainer()->bindSingleton(
            $alias,
            $mock = \Mockery::mock($interface ?? $alias)
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
            array_merge($current, $env)
        );

        $this->getContainer()->removeBinding(EnvironmentInterface::class);
        $this->getContainer()->bind(EnvironmentInterface::class, $this->environment);

        return $this;
    }

    public function assertEnvironmentValueSame(string $key, mixed $value): void
    {
        $currentValue = $this->getContainer()->get(EnvironmentInterface::class)->get($key);

        $this->assertSame(
            $currentValue,
            $value,
            \sprintf('Current environment value for key [%s] is [%s], expected [%s].', $key, $currentValue, $value)
        );
    }

    public function assertEnvironmentHasKey(string $key): void
    {
        $this->assertArrayHasKey(
            $key,
            $this->getContainer()->get(EnvironmentInterface::class)->getAll(),
            \sprintf('Environment does not have key with name [%s].', $key)
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
