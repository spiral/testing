<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Traits;

use Spiral\Core\Container;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Testing\Queue\FakeQueueManager;
use Spiral\Testing\Traits\InteractsWithQueue;
use Testo\Assert;
use Testo\Test;

/**
 * @coversDefaultClass InteractsWithQueue
 */
#[Test]
final class InteractsWithQueueTest
{
    public function test(): void
    {
        $container = new Container();
        $manager = new FakeQueueManager($container, new QueueConfig());
        $container->bind(FakeQueueManager::class, $manager);
        Assert::false($container->has(QueueConnectionProviderInterface::class));
        $object = $this->getSomeService($container);
        $queue = $object->fakeQueue();
        Assert::instanceOf($queue, FakeQueueManager::class);
        Assert::true($container->has(QueueConnectionProviderInterface::class));
        $queue2 = $object->fakeQueue();
        Assert::same($queue2, $queue);
    }

    private function getSomeService(Container $container): object
    {
        return new class($container) {
            use InteractsWithQueue;

            public function __construct(
                private readonly Container $container,
            ) {}

            public function getContainer(): Container
            {
                return $this->container;
            }
        };
    }
}
