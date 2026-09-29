<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Lifecycle;

use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Test;

final class SetUpOverrideTest extends TestCase
{
    private int $setUpCalls = 0;

    #[Test]
    public function testOverrideRunsOnce(): void
    {
        Assert::same($this->setUpCalls, 1);
    }

    #[Test]
    public function testEachTestGetsFreshInstance(): void
    {
        Assert::same($this->setUpCalls, 1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        ++$this->setUpCalls;
    }
}
