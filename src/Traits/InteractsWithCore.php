<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;

trait InteractsWithCore
{
    public function assertBootloaderRegistered(string $class): void
    {
        $this->assertContains(
            $class,
            $this->getRegisteredBootloaders(),
            \sprintf('Bootloader [%s] was not boot.', $class)
        );
    }

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
    ) {
        $this->assertContainerBound($alias, $class, $params);

        $this->assertSame(
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
     * @return class-string[]
     */
    public function getRegisteredBootloaders(): array
    {
        return $this->app->getRegisteredBootloaders();
    }

    /**
     * @param class-string|non-empty-string $alias
     * @param class-string|null $interface
     * @return \Mockery\MockInterface
     */
    public function mockContainer(string $alias, ?string $interface = null): \Mockery\MockInterface
    {
        $this->getContainer()->bindSingleton(
            $alias,
            $mock = \Mockery::mock($interface ?? $alias)
        );

        return $mock;
    }

    public function withEnvironment(array $env): self
    {
        $current = $this->getContainer()->get(EnvironmentInterface::class)->getAll();

        $this->environment = new Environment(
            array_merge($current, $env)
        );

        return $this;
    }
}
