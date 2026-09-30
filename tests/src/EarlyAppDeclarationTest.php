<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests;

use Spiral\Core\Container;
use Spiral\Testing\Attribute\BeforeBooting;
use Spiral\Testing\TestableKernelInterface;
use Testo\Assert;
use Testo\Test;

final class EarlyAppDeclarationTest extends TestCase
{
    private int $createdApps = 0;
    private int $bootingCalls = 0;
    private ?Container $bootingContainer = null;

    public function createAppInstance(Container $container = new Container()): TestableKernelInterface
    {
        ++$this->createdApps;

        return parent::createAppInstance($container);
    }

    #[BeforeBooting('captureContainer')]
    #[Test]
    public function testContainerIsAvailableWhileBooting(): void
    {
        Assert::same($this->createdApps, 1);
        Assert::same($this->bootingContainer, $this->getContainer());
    }

    private function captureContainer(): void
    {
        // Guards the recursion instead of letting it overflow the stack: a second call means
        // getContainer() did not find the app and went on to build another one.
        Assert::same(++$this->bootingCalls, 1);

        $this->bootingContainer = $this->getContainer();
    }
}
