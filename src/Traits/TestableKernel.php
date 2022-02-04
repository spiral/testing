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

    /** @return DispatcherInterface[] */
    public function getRegisteredDispatchers(): array
    {
        return $this->dispatchers;
    }

    /** @return array<class-string> */
    public function getRegisteredBootloaders(): array
    {
        return $this->bootloader->getClasses();
    }
}
