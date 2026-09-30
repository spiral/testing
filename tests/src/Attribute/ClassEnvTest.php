<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Testing\Attribute\Env;
use Spiral\Testing\Tests\TestCase;
use Testo\Test;

#[Env('FOO', 'CLASS')]
#[Env('BAR', 'CLASS')]
final class ClassEnvTest extends TestCase
{
    #[Test]
    public function testEnvFromClassAttribute(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'CLASS');
    }

    #[Env('FOO', 'METHOD')]
    #[Test]
    public function testMethodAttributeOverridesClass(): void
    {
        $this->assertEnvironmentValueSame('FOO', 'METHOD');
        $this->assertEnvironmentValueSame('BAR', 'CLASS');
    }
}
