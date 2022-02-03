<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\Files;

trait InteractsWithFileSystem
{
    public function assertDirectoryAliasDefined(string $name): void
    {
        $this->assertTrue(
            $this->getDirectories()->has($name),
            \sprintf('Application directory with alias [%s] is not defined.', $name)
        );
    }

    public function assertDirectoryAliasMatches(string $name, string $path): void
    {
        $this->assertDirectoryAliasDefined($name);

        $currentPath = $this->getDirectories()->get($name);

        $this->assertSame(
            $currentPath,
            $path,
            \sprintf(
                'Application directory with alias [%s] does not match [%s]. Current path is [%s]',
                $name,
                $path,
                $currentPath
            )
        );
    }

    public function getDirectories(): DirectoriesInterface
    {
        return $this->getContainer()->get(DirectoriesInterface::class);
    }

    public function getDirectoryByAlias(string $name, ?string $path = null): string
    {
        $dir = $this->getDirectories()->get($name);

        if ($path) {
            $dir = $dir.ltrim('/', $path);
        }

        return $dir;
    }

    public function cleanUpRuntimeDirectory(): void
    {
        $this->cleanupDirectories(
            $this->getDirectoryByAlias('runtime')
        );
    }

    public function cleanupDirectories(string ...$directories)
    {
        $fs = new Files();

        foreach ($directories as $directory) {
            if ($fs->isDirectory($directory)) {
                $fs->deleteDirectory($directory);
            }
        }
    }
}
