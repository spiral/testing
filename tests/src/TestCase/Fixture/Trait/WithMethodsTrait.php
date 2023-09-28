<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Fixture\Trait;

trait WithMethodsTrait
{
    public bool $calledSetUp = false;
    public bool $calledTearDown = false;

    public function setUpWithMethodsTrait(): void
    {
        $this->calledSetUp = true;
    }

    public function tearDownWithMethodsTrait(): void
    {
        $this->calledTearDown = true;
    }
}
