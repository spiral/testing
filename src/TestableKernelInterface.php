<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Core\Container;

interface TestableKernelInterface extends KernelInterface
{
    /**
     * Get application container
     */
    public function getContainer(): Container;

    /**
     * Get registered dispatchers
     * @return DispatcherInterface[]
     */
    public function getRegisteredDispatchers(): array;

    /**
     * Get registered and bootloaded bootloaders
     * @return array<class-string>
     */
    public function getRegisteredBootloaders(): array;
}
