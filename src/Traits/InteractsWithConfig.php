<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Console\Console;
use Spiral\Core\ConfigsInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait InteractsWithConfig
{
    public function assertConfigMatches(string $name, array $data)
    {
        $config = $this->getConfig($name);
        $this->assertSame($data, $config);
    }

    public function getConfig(string $config): array
    {
        return $this->getContainer()->get(ConfigsInterface::class)->getConfig($config);
    }

    public function setConfig(string $config, $data): void
    {
        $this->getContainer()->get(ConfigsInterface::class)->setDefaults(
            $config,
            $data
        );
    }
}
