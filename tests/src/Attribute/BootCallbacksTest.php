<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;
use Spiral\Testing\Attribute\BeforeBooting;
use Spiral\Testing\Attribute\BeforeInit;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[BeforeInit('recordInit')]
final class BootCallbacksTest extends TestCase
{
    /** @var list<non-empty-string> */
    private array $calls = [];

    public static function bindMarker(Container $container, EnvironmentInterface $env): void
    {
        $container->bindSingleton('marker', (object) ['env' => $env]);
    }

    #[BeforeBooting('recordBooting')]
    #[Test]
    public function testMethodsOfTestClassAreCalledInBootOrder(): void
    {
        Assert::same($this->calls, ['init', 'booting']);
    }

    #[BeforeBooting([self::class, 'bindMarker'])]
    #[Test]
    public function testStaticCallableGetsAutowiredArguments(): void
    {
        Assert::same($this->getContainer()->get('marker')->env, $this->getContainer()->get(EnvironmentInterface::class));
    }

    #[Test]
    public function testDeprecatedMethodFailsOnceTheAppHasBooted(): void
    {
        Expect::exception(\LogicException::class)->withMessageContaining('once the application has booted');

        $this->beforeBooting(static fn() => null);
    }

    private function recordInit(): void
    {
        $this->calls[] = 'init';
    }

    private function recordBooting(): void
    {
        $this->calls[] = 'booting';
    }
}
