<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Testing\Events\FakeEventDispatcher;

trait InteractsWithEvents
{
    public function fakeEventDispatcher(array $eventsToFake = []): FakeEventDispatcher
    {
        $this->getContainer()->removeBinding(EventDispatcherInterface::class);
        $this->getContainer()->bindSingleton(
            EventDispatcherInterface::class,
            $dispatcher = $this->getContainer()->make(
                FakeEventDispatcher::class,
                ['eventsToFake' => $eventsToFake]
            )
        );

        return $dispatcher;
    }
}
