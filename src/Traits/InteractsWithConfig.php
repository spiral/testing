<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Set;
use Spiral\Core\ConfigsInterface;
use Spiral\Testing\Attribute;
use Testo\Assert;
use Testo\Common\Attribute\AssertMethod;

trait InteractsWithConfig
{
    #[AssertMethod]
    public function assertConfigMatches(string $name, array $data): void
    {
        $config = $this->getConfig($name);

        Assert::same($config, $data);
    }

    #[AssertMethod]
    public function assertConfigHasFragments(string $name, array $data): void
    {
        $config = $this->getConfig($name);

        foreach ($data as $key => $fragment) {
            Assert::same($config[$key], $fragment);
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

    #[AssertMethod]
    public function setConfig(string $config, array $data): void
    {
        $this->getConfigurator()->setDefaults($config, $data);
    }

    #[AssertMethod]
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
