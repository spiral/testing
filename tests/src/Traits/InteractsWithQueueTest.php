<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Testing\Queue\FakeQueueManager;
use Spiral\Testing\Traits\InteractsWithQueue;

/**
 * @coversDefaultClass InteractsWithQueue
 */
final class InteractsWithQueueTest extends TestCase
{
    public function test(): void
    {
        $container = new Container();
        $manager = new FakeQueueManager($container, new QueueConfig());
        $container->bind(FakeQueueManager::class, $manager);
        self::assertFalse($container->has(QueueConnectionProviderInterface::class));

        $object = $this->getSomeService($container);
        $queue = $object->fakeQueue();
        self::assertInstanceOf(FakeQueueManager::class, $queue);
        self::assertTrue($container->has(QueueConnectionProviderInterface::class));
        $queue2 = $object->fakeQueue();
        self::assertSame($queue, $queue2);
    }

    private function getSomeService(Container $container):object
    {
        return new class ($container) {
            use InteractsWithQueue;

            public function __construct(
                private readonly Container $container
            )  {
            }

            public function getContainer(): Container
            {
                return $this->container;
            }
        };
    }
}
