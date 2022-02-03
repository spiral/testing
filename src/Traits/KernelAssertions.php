<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

trait KernelAssertions
{
    public function assertBootloaderLoaded(string $class): void
    {
        $this->assertContains($class, $this->getLoadedBootloaders());
    }

    public function assertBootloaderMissed(string $class): void
    {
        $this->assertNotContains($class, $this->getLoadedBootloaders());
    }

    public function assertDispatcherMissed(string $class): void
    {
        $this->assertNotContains($class, $this->getLoadedDispatchers());
    }

    public function assertDispatcherLoaded(string $class): void
    {
        $this->assertContains($class, $this->getLoadedDispatchers());
    }

    public function assertContainerBound(string $alias, string $class): void
    {
        $this->assertInstanceOf(
            $class,
            $this->getContainer()->get($alias)
        );
    }

    public function assertContainerBoundAsSingleton(string $alias, string $class): void
    {
        $this->assertInstanceOf(
            $class,
            $object = $this->getContainer()->get($alias)
        );

        $this->assertSame($object, $this->getContainer()->get($alias));
    }

    /**
     * @return class-string[]
     */
    public function getLoadedDispatchers(): array
    {
        return array_map(static function ($dispatcher) {
            return get_class($dispatcher);
        }, $this->app->getLoadedDispatchers());
    }

    /**
     * @return class-string[]
     */
    public function getLoadedBootloaders(): array
    {
        return $this->app->getLoadedBootloaders();
    }

    /**
     * @param class-string $alias
     * @param \Closure|null $closure
     * @return \Mockery\MockInterface
     */
    public function mockAlias(string $alias, \Closure $closure = null): \Mockery\MockInterface
    {
        $this->getContainer()->bind($alias, $mock = \Mockery::mock($alias));

        if ($closure) {
            $closure($mock);
        }

        return $mock;
    }
}
