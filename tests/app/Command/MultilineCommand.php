<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Command;

use Spiral\Console\Command;

final class MultilineCommand extends Command
{
    protected const NAME = 'multiline';

    public function perform(): int
    {
        $this->writeln('first line');
        $this->writeln('second line');

        return self::SUCCESS;
    }
}
