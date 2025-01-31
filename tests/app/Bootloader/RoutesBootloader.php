<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Bootloader;

use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Testing\Tests\App\Middleware\ErrorHandlerMiddleware;
use Spiral\Testing\Tests\App\Middleware\FailMiddleware;

final class RoutesBootloader extends BaseRoutesBootloader
{
    protected function globalMiddleware(): array
    {
        return [
            ErrorHandlerMiddleware::class,
            FailMiddleware::class,
        ];
    }

    protected function middlewareGroups(): array
    {
        return [];
    }
}
