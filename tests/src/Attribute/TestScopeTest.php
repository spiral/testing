<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Core\Internal\Introspector;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Test;

final class TestScopeTest extends TestCase
{
    #[Test]
    public function testDefaultScope(): void
    {
        Assert::same(Introspector::scopeNames($this->getContainer()), ['root']);
    }

    #[TestScope('foo')]
    #[Test]
    public function testScopeFromAttribute(): void
    {
        Assert::same(Introspector::scopeNames($this->getContainer()), ['foo', 'root']);
    }

    #[TestScope(['foo', 'bar'])]
    #[Test]
    public function testNestedScopes(): void
    {
        Assert::same(Introspector::scopeNames($this->getContainer()), ['bar', 'foo', 'root']);
    }

    #[TestScope('foo', ['test' => \stdClass::class])]
    #[Test]
    public function testScopeWithBindings(): void
    {
        Assert::same(Introspector::scopeNames($this->getContainer()), ['foo', 'root']);
        Assert::instanceOf($this->getContainer()->get('test'), \stdClass::class);
    }
}
