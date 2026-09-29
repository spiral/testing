<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Core\Internal\Introspector;
use Spiral\Testing\Attribute\Env;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Test;

final class EnvTest extends TestCase
{
    public const ENV = [
        'FOO' => 'BAR',
        'BAZ' => 'QUX',
    ];

    #[Test]
    public function testDefaultEnv(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'BAR');
        $this->assertEnvironmentValueSame('BAZ', 'QUX');
    }

    #[Env('FOO', 'BAZ')]
    #[Test]
    public function testEnvFromAttribute(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'BAZ');
        $this->assertEnvironmentValueSame('BAZ', 'QUX');
    }

    #[Env('FOO', 'BAZ')]
    #[Env('BAZ', 'BAZ')]
    #[Test]
    public function testMultipleAttributes(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'BAZ');
        $this->assertEnvironmentValueSame('BAZ', 'BAZ');
    }

    #[TestScope('foo')]
    #[Env('FOO', 'BAZ')]
    #[Test]
    public function testEnvFromAttributeInScope(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'BAZ');
        $this->assertEnvironmentValueSame('BAZ', 'QUX');
        Assert::same(Introspector::scopeNames($this->getContainer()), ['foo', 'root']);
    }

    #[TestScope(['foo', 'bar'])]
    #[Env('FOO', 'BAZ')]
    #[Test]
    public function testEnvFromAttributeInNestedScope(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'BAZ');
        $this->assertEnvironmentValueSame('BAZ', 'QUX');
        Assert::same(Introspector::scopeNames($this->getContainer()), ['bar', 'foo', 'root']);
    }
}
