<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Core;

use PHPUnit\Framework\ExpectationFailedException;
use Spiral\Testing\Tests\App\Repositories\ArrayPostRepository;
use Spiral\Testing\Tests\App\Repositories\PostRepositoryInterface;
use Spiral\Testing\Tests\App\Services\BlogService;
use Spiral\Testing\Tests\App\Services\BlogServiceInterface;
use Spiral\Testing\Tests\TestCase;

final class BootloaderTest extends TestCase
{
    public function testPostRepositoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            PostRepositoryInterface::class,
            ArrayPostRepository::class
        );
    }

    public function testBlogServiceInterfaceBinding(): void
    {
        $this->assertContainerBound(
            BlogServiceInterface::class,
            BlogService::class
        );
    }

    public function testBlogServiceInterfaceIsNotSingleton(): void
    {
        $this->assertContainerBoundNotAsSingleton(
            BlogServiceInterface::class,
            BlogService::class
        );
    }

    public function testAssertContainerBoundAsSingletonShouldThrowAnException(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Container [%s] is bound, but it contains not a singleton.',
                BlogServiceInterface::class
            )
        );

        $this->assertContainerBoundAsSingleton(
            BlogServiceInterface::class,
            BlogService::class
        );
    }
}
