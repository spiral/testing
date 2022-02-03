<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Console\Console;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait ConsoleAssertions
{
    public function assertConsoleCommandOutputContains(string $command, array $args = [], string ... $strings)
    {
        $output = $this->runCommand($command, $args);

        foreach ($strings as $string) {
            $this->assertStringContainsString($output, $string);
        }
    }

    public function getConsole(): Console
    {
        return $this->getContainer()->get(Console::class);
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

    public function runCommandDebug(string $command, array $args = [], OutputInterface $output = null): string
    {
        return $this->runCommand(
            $command,
            $args,
            $output,
            BufferedOutput::VERBOSITY_VERBOSE
        );
    }

    public function runCommandVeryVerbose(string $command, array $args = [], OutputInterface $output = null): string
    {
        return $this->runCommand(
            $command,
            $args,
            $output,
            BufferedOutput::VERBOSITY_DEBUG
        );
    }
}
