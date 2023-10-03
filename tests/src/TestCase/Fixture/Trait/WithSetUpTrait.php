<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Fixture\Trait;

trait WithSetUpTrait
{
    public bool $calledSetUp = false;

    public function setUpWithSetUpTrait(): void
    {
        $this->calledSetUp = true;
    }
}
