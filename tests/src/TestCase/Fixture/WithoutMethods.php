<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Fixture;

use Spiral\Testing\TestCase;
use Spiral\Testing\Tests\TestCase\Fixture\Trait\WithoutMethodsTrait;

final class WithoutMethods extends TestCase
{
    use WithoutMethodsTrait;
}
