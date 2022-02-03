<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\Files;

trait FileSystemAssertions
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

    public function getDirectoryByAlias(string $name): string
    {
        return $this->getDirectories()->get($name);
    }

    public function cleanUpRuntimeDirectories(): void
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
