<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Fixture\Trait;

trait WithTearDownTrait
{
    public bool $calledTearDown = false;

    public function tearDownWithTearDownTrait(): void
    {
        $this->calledTearDown = true;
    }
}
