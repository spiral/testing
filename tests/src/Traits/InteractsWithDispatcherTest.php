<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Traits;

use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Testing\TestCase;
use Spiral\Testing\Traits\InteractsWithDispatcher;
use Testo\Assert;
use Testo\Test;

/**
 * @coversDefaultClass InteractsWithDispatcher
 */
final class InteractsWithDispatcherTest extends TestCase
{
    #[Test]
    public function testAssertDispatcherCanBeServed(): void
    {
        $dispatcher = new class implements DispatcherInterface {
            public function canServe(): bool
            {
                return true;
            }

            public function serve(): void {}
        };

        $this->assertDispatcherCanBeServed($dispatcher::class);
    }

    #[Test]
    public function testAssertDispatcherCanBeServedStaticMethodWithEnv(): void
    {
        $dispatcher = new class {
            public function canServe(EnvironmentInterface $env): bool
            {
                return $env->get('MODE') === 'http';
            }
        };

        $this->getContainer()->bindSingleton(EnvironmentInterface::class, new Environment(['MODE' => 'http']), true);
        $this->assertDispatcherCanBeServed($dispatcher::class);
    }

    #[Test]
    public function testAssertDispatcherCannotBeServed(): void
    {
        $dispatcher = new class implements DispatcherInterface {
            public function canServe(): bool
            {
                return false;
            }

            public function serve(): void {}
        };

        $this->assertDispatcherCannotBeServed($dispatcher::class);
    }

    #[Test]
    public function testAssertDispatcherCannotBeServedStaticMethodWithEnv(): void
    {
        $dispatcher = new class {
            public function canServe(EnvironmentInterface $env): bool
            {
                return $env->get('MODE') === 'http';
            }
        };

        $this->getContainer()->bindSingleton(EnvironmentInterface::class, new Environment(['MODE' => 'jobs']), true);
        $this->assertDispatcherCannotBeServed($dispatcher::class);
    }

    #[Test]
    public function testGetRegisteredDispatchers(): void
    {
        $dispatcherA = \Mockery::mock(DispatcherInterface::class)->shouldIgnoreMissing();
        $dispatcherB = \Mockery::mock(DispatcherInterface::class)->shouldIgnoreMissing();

        $ref = new \ReflectionProperty($this->getApp(), 'dispatchers');
        $ref->setValue($this->getApp(), [$dispatcherA, $dispatcherB::class]);

        Assert::same($this->getRegisteredDispatchers(), [$dispatcherA::class, $dispatcherB::class]);
    }
}
