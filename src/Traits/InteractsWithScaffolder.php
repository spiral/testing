<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait InteractsWithScaffolder
{
    public function assertScaffolderCommandSame(
        string $command,
        array $args,
        string $expected,
        ?string $expectedFilename = null,
        array $expectedOutputStrings = [],
    ): void {
        $output = $this->mockScaffolder($command, $args, function (
            string $filename,
            string $data,
        ) use ($expected, $expectedFilename): bool {
            $this->assertSame($expected, $data, 'Generated code is not the same with expected.');

            if ($expectedFilename) {
                $this->assertSame($expectedFilename, $filename, 'Generated filename is not the same with expected.');
            }

            return true;
        });

        foreach ($expectedOutputStrings as $expected) {
            $this->assertStringContainsString($expected, $output, 'Output does not contain expected string.');
        }
    }

    public function assertScaffolderCommandContains(
        string $command,
        array $args,
        array $expectedStrings,
        ?string $expectedFilename = null,
        array $expectedOutputStrings = [],
    ): void {
        $output = $this->mockScaffolder($command, $args, function (
            string $filename,
            string $data,
        ) use ($expectedStrings, $expectedFilename): bool {
            foreach ($expectedStrings as $expected) {
                $this->assertStringContainsString($expected, $data, 'Generated code does not contain expected string.');
            }

            if ($expectedFilename) {
                $this->assertSame($expectedFilename, $filename, 'Generated filename is not the same with expected.');
            }

            return true;
        });

        foreach ($expectedOutputStrings as $expected) {
            $this->assertStringContainsString($expected, $output, 'Output does not contain expected string.');
        }
    }

    public function mockScaffolder(string $command, array $args, \Closure $expected): string
    {
        $originalFiles = $this->getContainer()->has(FilesInterface::class)
            ? $this->getContainer()->get(FilesInterface::class)
            : null;

        $files = $this->mockContainer(FilesInterface::class);
        $files->shouldReceive('normalizePath')->andReturnUsing(function (string $filename) {
            $root = $this->getDirectoryByAlias('root');

            return \str_replace($root, '', $filename);
        });
        $files->shouldReceive('exists')->andReturnFalse();
        $files->shouldReceive('write')->withArgs($expected);

        $this->getConsole()->run($command, new ArrayInput($args), $output = new BufferedOutput());

        $originalFiles !== null
            ? $this->getContainer()->bind(FilesInterface::class, $originalFiles)
            : $this->getContainer()->removeBinding(FilesInterface::class);

        return $output->fetch();
    }
}
