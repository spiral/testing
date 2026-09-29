<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Core\Internal\Introspector;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Testing\Attribute\Config;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Test;

final class ConfigTest extends TestCase
{
    #[Test]
    public function testDefaultSettings(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        Assert::same($config['default'], 'uploads');
    }

    #[Config('storage.default', 'replaced')]
    #[Test]
    public function testReplaceUsingAttribute(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        Assert::same($config['default'], 'replaced');
    }

    #[Config('storage.default', 'replaced')]
    #[Config('storage.servers.static.directory', 'test')]
    #[Test]
    public function testMultipleAttributes(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        Assert::same($config['default'], 'replaced');
        Assert::same($config['servers']['static']['directory'], 'test');
    }

    #[TestScope('foo')]
    #[Config('storage.default', 'replaced')]
    #[Test]
    public function testReplaceUsingAttributeInScope(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        Assert::same($config['default'], 'replaced');
        Assert::same(Introspector::scopeNames($this->getContainer()), ['foo', 'root']);
    }

    #[TestScope(['foo', 'bar'])]
    #[Config('storage.default', 'replaced')]
    #[Test]
    public function testReplaceUsingAttributeInNestedScope(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        Assert::same($config['default'], 'replaced');
        Assert::same(Introspector::scopeNames($this->getContainer()), ['bar', 'foo', 'root']);
    }
}
