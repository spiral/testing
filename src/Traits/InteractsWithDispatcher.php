<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\DispatcherInterface;

trait InteractsWithDispatcher
{
    public function serveDispatcher(string $dispatcher, array $bindings = []): void
    {
        $this->assertDispatcherRegistered($dispatcher);

        $this->runScoped(function () use ($dispatcher) {
            $this->getContainer()->removeBinding($dispatcher);

            /** @var DispatcherInterface $object */
            $object = $this->getContainer()->get($dispatcher);

            $object->serve();
        }, $bindings);
    }

    public function assertDispatcherRegistered(string $class): void
    {
        $this->assertContains(
            $class,
            $this->getRegisteredDispatchers(),
            \sprintf('Dispatcher [%s] was not loaded.', $class)
        );
    }

    public function assertDispatcherMissed(string $class): void
    {
        $this->assertNotContains(
            $class,
            $this->getRegisteredDispatchers(),
            \sprintf('Dispatcher [%s] was loaded.', $class)
        );
    }

    /**
     * @return class-string<DispatcherInterface>[]
     */
    public function getRegisteredDispatchers(): array
    {
        return array_map(static function ($dispatcher): string {
            return get_class($dispatcher);
        }, $this->app->getRegisteredDispatchers());
    }
}
