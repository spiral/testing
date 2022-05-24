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
     * @param array $bootloaders
     * @return $this
     */
    public function withBootloaders(array $bootloaders): self
    {
        $self = clone $this;
        $self->bootloader->bootload($bootloaders);

        return $self;
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
     * @param array{root: string}|array<non-empty-string, string> $directories
     * @return array{
     *     app: string,
     *     public: string,
     *     vendor: string,
     *     runtime: string,
     *     cache: string,
     *     config: string,
     *     resources: string,
     * }|array<non-empty-string, string>
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
