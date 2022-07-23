<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Console\Console;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait InteractsWithConsole
{
    public int $defaultVerbosityLevel = OutputInterface::VERBOSITY_NORMAL;

    /**
     * @param string[]|string $strings
     */
    public function assertConsoleCommandOutputContainsStrings(
        string $command,
        array $args = [],
        array|string $strings = [],
        ?int $verbosityLevel = null
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

    public function assertCommandRegistered(string $name): void
    {
        $this->assertTrue(
            $this->getConsole()->getApplication()->has($name),
            \sprintf('Command [%s] is not registered.', $name)
        );
    }

    final public function runCommand(
        string $command,
        array $args = [],
        OutputInterface $output = null,
        ?int $verbosityLevel = null
    ): string {
        $input = new ArrayInput($args);
        $output = $output ?? new BufferedOutput();
        $output->setVerbosity($verbosityLevel ?? $this->defaultVerbosityLevel);

        $this->getConsole()->run($command, $input, $output);

        return $output->fetch();
    }

    final public function getConsole(): Console
    {
        return $this->getContainer()->get(Console::class);
    }
}
