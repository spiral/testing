<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Testing\Attribute\WithoutExceptionHandling;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Test;

final class WithoutExceptionHandlingTest extends TestCase
{
    #[Test]
    public function testDefaultHandler(): void
    {
        Assert::instanceOf($this->getContainer()->get(ExceptionHandlerInterface::class), ExceptionHandler::class);
    }

    #[Test]
    public function testSuppressWithMethod(): void
    {
        $this->withoutExceptionHandling();

        Assert::false($this->getContainer()->get(ExceptionHandlerInterface::class) instanceof ExceptionHandler);
    }

    #[WithoutExceptionHandling]
    #[Test]
    public function testSuppressWithAttribute(): void
    {
        Assert::false($this->getContainer()->get(ExceptionHandlerInterface::class) instanceof ExceptionHandler);
    }
}
