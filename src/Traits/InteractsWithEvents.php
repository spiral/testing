<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Testing\Events\FakeEventDispatcher;

trait InteractsWithEvents
{
    public function fakeEventDispatcher(): FakeEventDispatcher
    {
        $this->getContainer()->bindSingleton(
            EventDispatcherInterface::class,
            $dispatcher = $this->getContainer()->get(FakeEventDispatcher::class)
        );

        return $dispatcher;
    }
}
