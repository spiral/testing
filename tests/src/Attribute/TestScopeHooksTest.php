<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Attribute;

use Spiral\Core\Internal\Introspector;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Tests\Attribute\Stub\RecordScope;
use Spiral\Testing\Tests\Attribute\Stub\RecordsScope;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * Lifecycle hooks and the interceptors of the scoped stage run inside the test scope.
 */
#[TestScope('foo')]
final class TestScopeHooksTest extends TestCase implements RecordsScope
{
    /** @var list<string> */
    private array $hookScopes = [];

    /** @var list<string> */
    private array $interceptorScopes = [];

    #[Test]
    public function testHookRunsInsideClassScope(): void
    {
        Assert::same($this->hookScopes, ['foo', 'root']);
    }

    #[TestScope('bar')]
    #[Test]
    public function testMethodScopeReplacesClassScope(): void
    {
        Assert::same($this->hookScopes, ['bar', 'root']);
    }

    #[RecordScope]
    #[Test]
    public function testScopedInterceptorSeesTheScope(): void
    {
        Assert::same($this->interceptorScopes, ['foo', 'root']);
    }

    public function recordInterceptorScopes(array $scopes): void
    {
        $this->interceptorScopes = $scopes;
    }

    #[BeforeTest]
    protected function recordHookScope(): void
    {
        $this->hookScopes = Introspector::scopeNames($this->getContainer());
    }
}
