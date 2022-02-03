<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Core\Container;

trait TestableKernel
{
    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRegisteredDispatchers(): array
    {
        return $this->dispatchers;
    }

    public function getRegisteredBootloaders(): array
    {
        return $this->bootloader->getClasses();
    }
}
