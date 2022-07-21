<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Testing\Tests\App\Repositories\ArrayPostRepository;
use Spiral\Testing\Tests\App\Repositories\PostRepositoryInterface;
use Spiral\Testing\Tests\App\Services\BlogService;
use Spiral\Testing\Tests\App\Services\BlogServiceInterface;

final class BlogBootloader extends Bootloader
{
    protected const BINDINGS = [
        BlogServiceInterface::class => BlogService::class
    ];

    protected const SINGLETONS = [
        PostRepositoryInterface::class => [self::class, 'initPostRepository']
    ];

    protected function initPostRepository(): PostRepositoryInterface
    {
        return new ArrayPostRepository([
            [
                'title' => 'foo',
                'text' => 'bar'
            ],
            [
                'title' => 'foo1',
                'text' => 'bar1'
            ],
            [
                'title' => 'foo2',
                'text' => 'bar2'
            ]
        ]);
    }
}
