<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Spiral\Testing\Tests\TestCase\Fixture\WithMethods;
use Spiral\Testing\Tests\TestCase\Fixture\WithMethodsInNestedParent;
use Spiral\Testing\Tests\TestCase\Fixture\WithMethodsInParent;
use Spiral\Testing\Tests\TestCase\Fixture\WithoutMethods;
use Spiral\Testing\Tests\TestCase\Fixture\WithoutTraits;
use Spiral\Testing\Tests\TestCase\Fixture\WithSetUp;
use Spiral\Testing\Tests\TestCase\Fixture\WithTearDown;

final class TestCaseTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    #[DoesNotPerformAssertions]
    public function testItDoesNotThrowWhenCallingSetUp(): void
    {
        $testCase = new WithoutTraits('foo');
        $testCase->setUp();
    }

    /**
     * @doesNotPerformAssertions
     */
    #[DoesNotPerformAssertions]
    public function testItDoesNotThrowWhenCallingTearDown(): void
    {
        $testCase = new WithoutTraits('foo');
        $testCase->tearDown();
    }

    public function testTraitWithoutMethods(): void
    {
        $testCase = new WithoutMethods('foo');
        $testCase->setUp();
        $testCase->tearDown();

        $this->assertTrue($testCase->isAvailable());
    }

    public function testTraitWithSetUp(): void
    {
        $testCase = new WithSetUp('foo');
        $testCase->setUp();
        $testCase->tearDown();

        $this->assertTrue($testCase->calledSetUp);
    }

    public function testTraitWithTearDown(): void
    {
        $testCase = new WithTearDown('foo');
        $testCase->setUp();
        $testCase->tearDown();

        $this->assertTrue($testCase->calledTearDown);
    }

    public function testTraitWithSetUpAndTearDownMethods(): void
    {
        $testCase = new WithMethods('foo');
        $testCase->setUp();
        $testCase->tearDown();

        $this->assertTrue($testCase->calledSetUp);
        $this->assertTrue($testCase->calledTearDown);
    }

    public function testTraitWithSetUpAndTearDownMethodsInParentClass(): void
    {
        $testCase = new WithMethodsInParent('foo');
        $testCase->setUp();
        $testCase->tearDown();

        $this->assertTrue($testCase->calledSetUp);
        $this->assertTrue($testCase->calledTearDown);
    }

    public function testTraitWithSetUpAndTearDownMethodsInNestedParentClass(): void
    {
        $testCase = new WithMethodsInNestedParent('foo');
        $testCase->setUp();
        $testCase->tearDown();

        $this->assertTrue($testCase->calledSetUp);
        $this->assertTrue($testCase->calledTearDown);
    }
}
