<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\TestCase;

use Spiral\Testing\TestCase;
use Spiral\Testing\Tests\TestCase\Fixture\WithMethods;
use Spiral\Testing\Tests\TestCase\Fixture\WithMethodsInNestedParent;
use Spiral\Testing\Tests\TestCase\Fixture\WithMethodsInParent;
use Spiral\Testing\Tests\TestCase\Fixture\WithoutMethods;
use Spiral\Testing\Tests\TestCase\Fixture\WithoutTraits;
use Spiral\Testing\Tests\TestCase\Fixture\WithSetUp;
use Spiral\Testing\Tests\TestCase\Fixture\WithTearDown;
use Testo\Assert;
use Testo\Assert\ExpectNoAssertions;
use Testo\Test;

#[Test]
final class TestCaseTest
{
    #[ExpectNoAssertions]
    public function testItDoesNotThrowWhenCallingSetUp(): void
    {
        $testCase = new WithoutTraits();
        self::setUp($testCase);
    }

    #[ExpectNoAssertions]
    public function testItDoesNotThrowWhenCallingTearDown(): void
    {
        $testCase = new WithoutTraits();
        self::tearDown($testCase);
    }

    public function testTraitWithoutMethods(): void
    {
        $testCase = new WithoutMethods();
        self::setUp($testCase);
        self::tearDown($testCase);
        Assert::true($testCase->isAvailable());
    }

    public function testTraitWithSetUp(): void
    {
        $testCase = new WithSetUp();
        self::setUp($testCase);
        self::tearDown($testCase);
        Assert::true($testCase->calledSetUp);
    }

    public function testTraitWithTearDown(): void
    {
        $testCase = new WithTearDown();
        self::setUp($testCase);
        self::tearDown($testCase);
        Assert::true($testCase->calledTearDown);
    }

    public function testTraitWithSetUpAndTearDownMethods(): void
    {
        $testCase = new WithMethods();
        self::setUp($testCase);
        self::tearDown($testCase);
        Assert::true($testCase->calledSetUp);
        Assert::true($testCase->calledTearDown);
    }

    public function testTraitWithSetUpAndTearDownMethodsInParentClass(): void
    {
        $testCase = new WithMethodsInParent();
        self::setUp($testCase);
        self::tearDown($testCase);
        Assert::true($testCase->calledSetUp);
        Assert::true($testCase->calledTearDown);
    }

    public function testTraitWithSetUpAndTearDownMethodsInNestedParentClass(): void
    {
        $testCase = new WithMethodsInNestedParent();
        self::setUp($testCase);
        self::tearDown($testCase);
        Assert::true($testCase->calledSetUp);
        Assert::true($testCase->calledTearDown);
    }

    private static function setUp(TestCase $testCase): void
    {
        (fn() => $this->setUp())->call($testCase);
    }

    private static function tearDown(TestCase $testCase): void
    {
        (fn() => $this->tearDown())->call($testCase);
    }
}
