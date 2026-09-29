<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Lifecycle;

use Spiral\Core\ContainerScope;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * Hooks of a subclass run between the app boot in setUp() and its shutdown in tearDown().
 */
final class HookOrderTest extends TestCase
{
    private bool $bootedBeforeHook = false;

    #[Test]
    public function testAppIsBootedBeforeSubclassHook(): void
    {
        Assert::true($this->bootedBeforeHook);
    }

    #[BeforeTest]
    protected function checkBooted(): void
    {
        # Read the scope directly: getContainer() would boot the app on demand and hide the order.
        $this->bootedBeforeHook = ContainerScope::getContainer() !== null;
    }

    #[AfterTest]
    protected function checkNotShutDown(): void
    {
        Assert::notNull(ContainerScope::getContainer());
    }
}
