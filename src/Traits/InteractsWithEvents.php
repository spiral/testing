<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Testing\Events\FakeEventDispatcher;

trait InteractsWithEvents
{
    /**
     * @param bool $decorate If false then original eventDispatcher will be fully replace by Fake, otherwise it will be decorated.
     */
    public function fakeEventDispatcher(array $eventsToFake = [], bool $decorate = false): FakeEventDispatcher
    {
        $eventDispatcher = null;
        if ($decorate) {
            $eventDispatcher = $this->getContainer()->get(EventDispatcherInterface::class);
        } else {
            $this->getContainer()->removeBinding(EventDispatcherInterface::class);
        }

        $this->getContainer()->bindSingleton(
            EventDispatcherInterface::class,
            $dispatcher = $this->getContainer()->make(
                FakeEventDispatcher::class,
                [
                    'eventDispatcher' => $eventDispatcher,
                    'eventsToFake' => $eventsToFake,
                ],
            ),
        );

        return $dispatcher;
    }
}
