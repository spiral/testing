<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Testing\Tests\App\Event\SomeEvent;

class YetSomeListener
{
    #[Listener]
    public function onSomeEvent(SomeEvent $event): void
    {
        var_dump($event);
    }
}
