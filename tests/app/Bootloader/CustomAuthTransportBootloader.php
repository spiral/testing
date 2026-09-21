<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Bootloader;

use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Auth\HttpAuthBootloader;

/**
 * Registers an auth transport under a name that does not exist in the default auth config.
 */
final class CustomAuthTransportBootloader extends Bootloader
{
    public const TRANSPORT = 'custom';

    public function defineDependencies(): array
    {
        return [HttpAuthBootloader::class];
    }

    public function init(HttpAuthBootloader $auth): void
    {
        $auth->addTransport(self::TRANSPORT, new HeaderTransport(header: 'X-Custom-Token'));
    }
}
