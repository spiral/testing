<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\KernelInterface;

trait InteractsWithDispatcher
{
    /**
     * @param class-string<DispatcherInterface> $dispatcher
     */
    public function assertDispatcherCanBeServed(string $dispatcher): void
    {
        $this->assertTrue(
            $this->getContainer()->invoke([$dispatcher, 'canServe']),
            \sprintf('Dispatcher [%s] can not be served.', $dispatcher)
        );
    }

    /**
     * @param class-string<DispatcherInterface> $dispatcher
     */
    public function assertDispatcherCannotBeServed(string $dispatcher): void
    {
        $this->assertFalse(
            $this->getContainer()->invoke([$dispatcher, 'canServe']),
            \sprintf('Dispatcher [%s] can be served.', $dispatcher)
        );
    }

    /**
     * @param class-string<DispatcherInterface> $dispatcher
     */
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

    /**
     * @param class-string<DispatcherInterface> $dispatcher
     */
    public function assertDispatcherRegistered(string $dispatcher): void
    {
        $this->assertContains(
            $dispatcher,
            $this->getRegisteredDispatchers(),
            \sprintf('Dispatcher [%s] was not loaded.', $dispatcher)
        );
    }

    /**
     * @param class-string<DispatcherInterface> $dispatcher
     */
    public function assertDispatcherMissed(string $dispatcher): void
    {
        $this->assertNotContains(
            $dispatcher,
            $this->getRegisteredDispatchers(),
            \sprintf('Dispatcher [%s] was loaded.', $dispatcher)
        );
    }

    /**
     * @return class-string<DispatcherInterface>[]
     */
    public function getRegisteredDispatchers(): array
    {
        return \array_map(static function ($dispatcher): string {
            return \is_object($dispatcher) ? $dispatcher::class : $dispatcher;
        }, $this->getContainer()->get(KernelInterface::class)->getRegisteredDispatchers());
    }
}
