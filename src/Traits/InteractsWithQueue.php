<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Testing\Queue\FakeQueueManager;

trait InteractsWithQueue
{
    public function fakeQueue(): FakeQueueManager
    {
        $container = $this->getContainer();
        if ($container->has(QueueConnectionProviderInterface::class)) {
            $manager = $container->get(QueueConnectionProviderInterface::class);
            if ($manager instanceof FakeQueueManager) {
                return $manager;
            }
        }

        $container->removeBinding(QueueConnectionProviderInterface::class);
        $container->bindSingleton(
            QueueConnectionProviderInterface::class,
            $manager = $container->get(FakeQueueManager::class)
        );

        return $manager;
    }
}
