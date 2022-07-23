<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Command;

use Spiral\Console\Command;

final class FooCommand extends Command
{
    protected const NAME = 'foo';
}
