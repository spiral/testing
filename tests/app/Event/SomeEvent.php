<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Event;

class SomeEvent
{
    public function __construct(
        public readonly int $someParam
    ) {
    }
}
