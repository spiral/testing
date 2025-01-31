<?php

namespace Spiral\Testing\Tests;

use Spiral\Testing\Tests\App\Bootloader\BlogBootloader;
use Spiral\Testing\Tests\App\Bootloader\MiddlewareBootloader;
use Spiral\Testing\Tests\App\Bootloader\RoutesBootloader;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__ . '/../';
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
            \Spiral\Events\Bootloader\EventsBootloader::class,
            \Spiral\League\Event\Bootloader\EventBootloader::class,
            \Spiral\Scaffolder\Bootloader\ScaffolderBootloader::class,
            BlogBootloader::class,
            RoutesBootloader::class,
            MiddlewareBootloader::class,
            // ...
        ];
    }
}
