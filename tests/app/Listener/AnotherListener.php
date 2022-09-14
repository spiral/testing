<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Testing\Tests\App\Event\AnotherEvent;

class AnotherListener
{
    #[Listener]
    public function ooAnotherEvent(AnotherEvent $event): void
    {
        var_dump($event);
    }
}
