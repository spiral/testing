<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Testing\Attribute\WithoutExceptionHandling;
use Spiral\Testing\Tests\TestCase;

final class WithoutExceptionHandlingTest extends TestCase
{
    public function testDefaultHandler(): void
    {
        $this->assertInstanceOf(
            ExceptionHandler::class,
            $this->getContainer()->get(ExceptionHandlerInterface::class)
        );
    }

    public function testSuppressWithMethod(): void
    {
        $this->withoutExceptionHandling();

        $this->assertNotInstanceOf(
            ExceptionHandler::class,
            $this->getContainer()->get(ExceptionHandlerInterface::class)
        );
    }

    #[WithoutExceptionHandling]
    public function testSuppressWithAttribute(): void
    {
        $this->assertNotInstanceOf(
            ExceptionHandler::class,
            $this->getContainer()->get(ExceptionHandlerInterface::class)
        );
    }
}
