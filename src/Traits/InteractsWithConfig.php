<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Set;
use Spiral\Core\ConfigsInterface;
use Spiral\Testing\Attribute;

trait InteractsWithConfig
{
    public function assertConfigMatches(string $name, array $data): void
    {
        $config = $this->getConfig($name);

        $this->assertSame($data, $config);
    }

    public function assertConfigHasFragments(string $name, array $data): void
    {
        $config = $this->getConfig($name);

        foreach ($data as $key => $fragment) {
            $this->assertSame($fragment, $config[$key]);
        }
    }

    public function getConfig(string $config): array
    {
        return $this->getConfigs()->getConfig($config);
    }

    public function getConfigurator(): ConfiguratorInterface
    {
        return $this->getContainer()->get(ConfiguratorInterface::class);
    }

    public function getConfigs(): ConfigsInterface
    {
        return $this->getContainer()->get(ConfigsInterface::class);
    }

    public function setConfig(string $config, array $data): void
    {
        $this->getConfigurator()->setDefaults($config, $data);
    }

    public function updateConfig(string $key, mixed $data): void
    {
        [$config, $key] = explode('.', $key, 2);

        $this->getConfigs()->modify($config, new Set($key, $data));
    }

    /**
     * @deprecated since v2.6.4
     */
    private function updateConfigFromAttribute(): void
    {
        foreach ($this->getTestAttributes(Attribute\Config::class) as $attribute) {
            \assert($attribute instanceof Attribute\Config);
            $this->updateConfig($attribute->path, $attribute->closure?->__invoke() ?? $attribute->value);
        }
    }
}
