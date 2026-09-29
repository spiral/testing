<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Core;

use Spiral\Testing\Tests\App\Repositories\ArrayPostRepository;
use Spiral\Testing\Tests\App\Repositories\PostRepositoryInterface;
use Spiral\Testing\Tests\App\Services\BlogService;
use Spiral\Testing\Tests\App\Services\BlogServiceInterface;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Expect;
use Testo\Test;

final class BootloaderTest extends TestCase
{
    #[Test]
    public function testPostRepositoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            PostRepositoryInterface::class,
            ArrayPostRepository::class,
        );
    }

    #[Test]
    public function testBlogServiceInterfaceBinding(): void
    {
        $this->assertContainerBound(
            BlogServiceInterface::class,
            BlogService::class,
        );
    }

    #[Test]
    public function testBlogServiceInterfaceIsNotSingleton(): void
    {
        $this->assertContainerBoundNotAsSingleton(
            BlogServiceInterface::class,
            BlogService::class,
        );
    }

    #[Test]
    public function testAssertContainerBoundAsSingletonShouldThrowAnException(): void
    {
        Expect::exception(AssertionException::class)->withMessageContaining(\sprintf(
            'Container [%s] is bound, but it contains not a singleton.',
            BlogServiceInterface::class,
        ));

        $this->assertContainerBoundAsSingleton(
            BlogServiceInterface::class,
            BlogService::class,
        );
    }
}
