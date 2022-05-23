<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\ConfigsInterface;

trait InteractsWithConfig
{
    public function assertConfigMatches(string $name, array $data): void
    {
        $config = $this->getConfig($name);
        $this->assertSame($data, $config);
    }

    public function getConfig(string $config): array
    {
        return $this->getContainer()->get(ConfigsInterface::class)->getConfig($config);
    }

    public function setConfig(string $config, array $data): void
    {
        $this->getContainer()->get(ConfiguratorInterface::class)->setDefaults(
            $config,
            $data
        );
    }
}
