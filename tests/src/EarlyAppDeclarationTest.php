<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests;

use Spiral\Core\Container;
use Spiral\Testing\TestableKernelInterface;

final class EarlyAppDeclarationTest extends TestCase
{
    public const MAKE_APP_ON_STARTUP = false;

    private int $createdApps = 0;
    private int $bootingCalls = 0;

    public function createAppInstance(Container $container = new Container()): TestableKernelInterface
    {
        ++$this->createdApps;

        return parent::createAppInstance($container);
    }

    public function testContainerIsAvailableWhileBooting(): void
    {
        $bootingContainer = null;

        $this->beforeBooting(function () use (&$bootingContainer): void {
            // Guards the recursion instead of letting it overflow the stack: a second call means
            // getContainer() did not find the app and went on to build another one.
            $this->assertSame(1, ++$this->bootingCalls);

            $bootingContainer = $this->getContainer();
        });

        $this->initApp();

        $this->assertSame(1, $this->createdApps);
        $this->assertSame($this->getContainer(), $bootingContainer);
    }
}
