<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests;

use Testo\Assert\State\Assertion\AssertionException;
use Testo\Expect;
use Testo\Test;

final class ConsoleTest extends TestCase
{
    #[Test]
    public function testRegisteredCommand(): void
    {
        $this->assertCommandRegistered('foo');
    }

    #[Test]
    public function testNotRegisteredCommandShouldThrowAnException(): void
    {
        Expect::exception(AssertionException::class)->withMessageContaining('Command [bar] is not registered.');

        $this->assertCommandRegistered('bar');
    }
}
