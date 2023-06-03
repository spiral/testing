<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests;

use PHPUnit\Framework\ExpectationFailedException;

final class ConsoleTest extends TestCase
{
    public function testRegisteredCommand(): void
    {
        $this->assertCommandRegistered('foo');
    }


    public function testNotRegisteredCommandShouldThrowAnException(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Command [bar] is not registered.');

        $this->assertCommandRegistered('bar');
    }
}
