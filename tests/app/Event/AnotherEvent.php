<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Event;

class AnotherEvent
{
    public function __construct(
        public readonly string $anotherParam
    ) {
    }
}
