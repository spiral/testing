<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\DispatcherInterface;
use Spiral\Core\Container;

trait TestableKernel
{
    /** @inheritDoc */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /** @return array<class-string<DispatcherInterface>> */
    public function getRegisteredDispatchers(): array
    {
        return \array_map(static fn (string|DispatcherInterface $dispatcher): string => \is_object($dispatcher)
            ? $dispatcher::class
            : $dispatcher,
            $this->dispatchers
        );
    }

    /** @return array<class-string> */
    public function getRegisteredBootloaders(): array
    {
        return $this->bootloader->getClasses();
    }
}
