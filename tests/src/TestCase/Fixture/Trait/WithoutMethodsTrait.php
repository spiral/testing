<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Fixture\Trait;

trait WithoutMethodsTrait
{
    public function isAvailable(): bool
    {
        return true;
    }
}
