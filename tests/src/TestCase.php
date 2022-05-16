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
            \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
            \Spiral\Tokenizer\Bootloader\TokenizerBootloader::class,
            \Spiral\SendIt\Bootloader\MailerBootloader::class,
            \Spiral\Bootloader\Http\HttpBootloader::class,
            \Spiral\Http\Bootloader\DiactorosBootloader::class,
            \Spiral\Bootloader\Security\EncrypterBootloader::class,
            \Spiral\Bootloader\Http\RouterBootloader::class,
            \Spiral\Router\Bootloader\AnnotatedRoutesBootloader::class,
            \Spiral\Storage\Bootloader\StorageBootloader::class,
            \Spiral\Testing\Tests\App\Bootloader\BlogBootloader::class,
            // ...
        ];
    }
}
