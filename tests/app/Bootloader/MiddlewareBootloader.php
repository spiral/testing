<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Testing\Tests\App\Middleware\FailMiddleware;

final class MiddlewareBootloader extends Bootloader
{
    public function init(BinderInterface $binder): void
    {
        $binder->getBinder('http')->bindSingleton(FailMiddleware::class, FailMiddleware::class);
    }
}
