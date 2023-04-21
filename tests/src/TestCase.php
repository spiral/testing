<?php

namespace Spiral\Testing\Tests;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__.'/../';
    }

    public function defineBootloaders(): array
    {
        return [
            \Spiral\Console\Bootloader\ConsoleBootloader::class,
            \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
            \Spiral\Tokenizer\Bootloader\TokenizerBootloader::class,
            \Spiral\SendIt\Bootloader\MailerBootloader::class,
            \Spiral\Bootloader\Http\HttpBootloader::class,
            \Spiral\Nyholm\Bootloader\NyholmBootloader::class,
            \Spiral\Bootloader\Security\EncrypterBootloader::class,
            \Spiral\Bootloader\Http\RouterBootloader::class,
            \Spiral\Router\Bootloader\AnnotatedRoutesBootloader::class,
            \Spiral\Storage\Bootloader\StorageBootloader::class,
            \Spiral\Testing\Tests\App\Bootloader\BlogBootloader::class,
            \Spiral\Events\Bootloader\EventsBootloader::class,
            \Spiral\League\Event\Bootloader\EventBootloader::class,
            \Spiral\Scaffolder\Bootloader\ScaffolderBootloader::class,
            // ...
        ];
    }
}
