<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Testing\Queue\FakeQueueManager;

trait InteractsWithQueue
{
    public function fakeQueue(): FakeQueueManager
    {
        $this->getContainer()->bindSingleton(
            QueueConnectionProviderInterface::class,
            $manager = $this->getContainer()->get(FakeQueueManager::class)
        );

        return $manager;
    }
}
