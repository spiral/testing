<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Storage\Config\StorageConfig;
use Spiral\Testing\Attribute\Config;
use Spiral\Testing\Tests\TestCase;

final class ConfigTest extends TestCase
{
    public function testDefaultSettings(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        $this->assertSame('uploads', $config['default']);
    }

    #[Config('storage.default', 'replaced')]
    public function testReplaceUsingAttribute(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        $this->assertSame('replaced', $config['default']);
    }

    #[Config('storage.default', 'replaced')]
    #[Config('storage.servers.static.adapter', 'test')]
    public function testMultipleAttributes(): void
    {
        $config = $this->getConfig(StorageConfig::CONFIG);
        $this->assertSame('replaced', $config['default']);
        $this->assertSame('test', $config['servers']['static']['adapter']);
    }
}
