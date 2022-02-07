<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Console\Console;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait InteractsWithConsole
{
    /**
     * @param string[]|string $strings
     */
    public function assertConsoleCommandOutputContainsStrings(
        string $command,
        array $args = [],
        $strings = []
    ): void {
        $output = $this->runCommand($command, $args);

        foreach ((array)$strings as $string) {
            $this->assertStringContainsString(
                $string,
                $output,
                \sprintf(
                    'Console command [%s] with args [%s] does not contain string [%s]',
                    $command,
                    json_encode($args),
                    $string
                )
            );
        }
    }

    public function runCommand(
        string $command,
        array $args = [],
        OutputInterface $output = null,
        ?int $verbosityLevel = null
    ): string {
        $input = new ArrayInput($args);
        $output = $output ?? new BufferedOutput();
        if ($verbosityLevel !== null) {
            $output->setVerbosity($verbosityLevel);
        }

        $this->getConsole()->run($command, $input, $output);

        return $output->fetch();
    }

    public function getConsole(): Console
    {
        return $this->getContainer()->get(Console::class);
    }
}
