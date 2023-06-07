<?php

declare(strict_types=1);

namespace Spiral\Testing\Queue;

use Spiral\Core\Container;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\QueueConnectionProviderInterface;

class FakeQueueManager implements QueueConnectionProviderInterface
{
    /**
     * @var array<FakeQueue>
     */
    private array $connections = [];

    public function __construct(
        private readonly Container $container,
        private readonly QueueConfig $config
    ) {
    }

    public function getConnection(?string $name = null): FakeQueue
    {
        $name = $name ?: $this->getDefaultDriver();
        $name = $this->config->getAliases()[$name] ?? $name;

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        $config = $this->config->getConnection($name);
        $config['name'] = $name;

        return $this->connections[$name] = $this->container->make(FakeQueue::class, $config);
    }

    private function getDefaultDriver(): string
    {
        return $this->config->getDefaultDriver();
    }

    public function clearAll(): void
    {
        foreach ($this->connections as $connection) {
            $connection->clear();
        }
    }
}
