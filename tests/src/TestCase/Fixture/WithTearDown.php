<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Fixture;

use Spiral\Testing\TestCase;
use Spiral\Testing\Tests\TestCase\Fixture\Trait\WithTearDownTrait;

final class WithTearDown extends TestCase
{
    use WithTearDownTrait;
}
