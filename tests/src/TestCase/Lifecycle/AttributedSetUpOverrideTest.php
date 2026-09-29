<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase\Lifecycle;

use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * An override that repeats the lifecycle attribute of the parent method still runs once.
 */
final class AttributedSetUpOverrideTest extends TestCase
{
    private int $setUpCalls = 0;

    #[Test]
    public function testOverrideRunsOnce(): void
    {
        Assert::same($this->setUpCalls, 1);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        parent::setUp();

        ++$this->setUpCalls;
    }
}
