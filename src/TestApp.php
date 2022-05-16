<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\Exception\BootException;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

class TestApp extends AbstractKernel implements TestableKernelInterface
{
    use Traits\TestableKernel;

    // framework specific bootloaders
    protected const SYSTEM = [
        CoreBootloader::class,
        TokenizerBootloader::class,
    ];

    // application specific bootloaders
    protected const APP = [];

    /**
     * @var array<class-string>
     */
    protected array $bootloaders;

    /**
     * Create an application instance with bootloader.
     * @throws \Throwable
     */
    public static function createWithBootloaders(
        array $bootloaders,
        array $directories,
        bool $handleErrors = true
    ): self {

        /** @var TestApp $kernel */
        $kernel = static::create($directories, $handleErrors);
        $kernel->bootloaders = $bootloaders;

        return $kernel;
    }

    public function defineBootloaders(): array
    {
        return $this->bootloaders;
    }

    /**
     * Each application can define it's own boot sequence.
     */
    protected function bootstrap(): void
    {
        $this->bootloader->bootload(static::APP);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (! isset($directories['root'])) {
            throw new BootException('Missing required directory `root`');
        }

        if (! isset($directories['app'])) {
            $directories['app'] = $directories['root'].'/app/';
        }

        return array_merge(
            [
                // public root
                'public' => $directories['root'].'/public/',

                // vendor libraries
                'vendor' => $directories['root'].'/vendor/',

                // data directories
                'runtime' => $directories['root'].'/runtime/',
                'cache' => $directories['root'].'/runtime/cache/',

                // application directories
                'config' => $directories['app'].'/config/',
                'resources' => $directories['app'].'/resources/',
            ],
            $directories
        );
    }
}
