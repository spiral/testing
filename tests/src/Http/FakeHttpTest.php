<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Internal\Introspector;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Tests\App\Middleware\FailMiddleware;
use Spiral\Testing\Tests\Http\Stub\FakeFinalizer;
use Spiral\Testing\Tests\Http\Stub\StaticResultMiddleware;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Expect;
use Testo\Test;

final class FakeHttpTest extends TestCase
{
    #[Test]
    public function testGetBodySame(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[]');
    }

    #[Test]
    public function testWithActor(): void
    {
        $http = $this->fakeHttp();

        $user = (object) ['id' => 42, 'name' => 'John Doe'];

        $response = $http
            ->withMiddleware(AuthMiddleware::class)
            ->withActor($user)
            ->get('/auth/actor');

        $response->assertOk();
        $response->assertBodySame('{"id":42,"name":"John Doe"}');
    }

    #[Test]
    public function testWithMiddleware(): void
    {
        $response = $this->fakeHttp()
            ->withMiddleware(StaticResultMiddleware::class)
            ->get('/get/query-params');
        $response->assertBodySame(StaticResultMiddleware::STATIC_RESULT);
    }

    #[Test]
    public function testWithoutMiddleware(): void
    {
        $this->fakeHttp()
            ->get(FailMiddleware::ROUTE)
            ->assertBodySame(FailMiddleware::STATIC_RESULT);

        $this->fakeHttp()
            ->withoutMiddleware(FailMiddleware::class)
            ->get(FailMiddleware::ROUTE)
            ->assertNotFound();

        $this->fakeHttp()
            ->get(FailMiddleware::ROUTE)
            ->assertBodySame(FailMiddleware::STATIC_RESULT);
    }

    #[Test]
    public function testWithoutAndWithMiddleware(): void
    {
        $this->fakeHttp()
            ->withMiddleware(StaticResultMiddleware::class)
            ->withoutMiddleware(StaticResultMiddleware::class)
            ->withMiddleware(StaticResultMiddleware::class)
            ->withoutMiddleware(StaticResultMiddleware::class)
            ->get('/')
            ->assertBodyNotSame(StaticResultMiddleware::STATIC_RESULT);

        $this->fakeHttp()
            ->withMiddleware(StaticResultMiddleware::class)
            ->withoutMiddleware(StaticResultMiddleware::class)
            ->withMiddleware(StaticResultMiddleware::class)
            ->get('/')
            ->assertBodySame(StaticResultMiddleware::STATIC_RESULT);

        $this->fakeHttp()
            ->withoutMiddleware(FailMiddleware::class)
            ->withMiddleware(FailMiddleware::class)
            ->withoutMiddleware(FailMiddleware::class)
            ->withMiddleware(FailMiddleware::class)
            ->get(FailMiddleware::ROUTE)
            ->assertBodySame(FailMiddleware::STATIC_RESULT);

        $this->fakeHttp()
            ->withoutMiddleware(FailMiddleware::class)
            ->withMiddleware(FailMiddleware::class)
            ->withoutMiddleware(FailMiddleware::class)
            ->get(FailMiddleware::ROUTE)
            ->assertNotFound();

        // No mutable state from the previous test
        $this->fakeHttp()
            ->get(FailMiddleware::ROUTE)
            ->assertBodySame(FailMiddleware::STATIC_RESULT);
    }

    #[TestScope('http')]
    #[Test]
    public function testHttpScopeDoesNotConflict(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[]');
    }

    #[Test]
    public function testAutoHttpScope(): void
    {
        $response = $this->fakeHttp()->get('/get/scopes');
        $response->assertBodySame('["http-request","http","root"]');
    }

    #[Test]
    public function testGetWithQueryParams(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params', ['foo' => 'bar', 'baz' => ['foo1' => 'bar1']]);
        $response->assertBodySame('{"foo":"bar","baz":{"foo1":"bar1"}}');
    }

    #[Test]
    public function testGetShouldThrowAnExceptionWhenNotSame(): void
    {
        Expect::exception(AssertionException::class)->withMessageContaining('Response is not same with [[foo]]');

        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[foo]');
    }

    #[Test]
    public function testGetWithHeaders(): void
    {
        $response = $this->fakeHttp()->get('/get/headers', headers: ['foo' => 'bar', 'baz=bar']);
        $response->assertBodySame('{"foo":["bar"],"0":["baz=bar"]}');
    }

    #[Test]
    public function testGetWithDefaultHeaders(): void
    {
        $http = $this->fakeHttp();
        $http->withHeaders(['baz' => 'bar']);
        $http->withHeader('foo', 'bar');

        $response = $http->get('/get/headers');
        $response->assertBodySame('{"baz":["bar"],"foo":["bar"]}');
    }

    #[Test]
    public function testGetJsonParsedBody(): void
    {
        $http = $this->fakeHttp();
        $arr = [
            'foo' => 'bar',
            'list' => [1, 2, 3, 4],
        ];
        $response = $http->get('/get/query-params', $arr);
        Assert::same($response->getJsonParsedBody(), $arr);
    }

    #[TestScope('foo')]
    #[Test]
    public function testGetWithQueryParamsInScope(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params', ['foo' => 'bar', 'baz' => ['foo1' => 'bar1']]);
        $response->assertBodySame('{"foo":"bar","baz":{"foo1":"bar1"}}');
        Assert::same(Introspector::scopeNames($this->getContainer()), ['foo', 'root']);
    }

    #[TestScope(['foo', 'bar'])]
    #[Test]
    public function testGetWithQueryParamsInNestedScope(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params', ['foo' => 'bar', 'baz' => ['foo1' => 'bar1']]);
        $response->assertBodySame('{"foo":"bar","baz":{"foo1":"bar1"}}');
        Assert::same(Introspector::scopeNames($this->getContainer()), ['bar', 'foo', 'root']);
    }

    #[Test]
    public function testFinalizersAreCalledAfterRequest(): void
    {
        $finalizer = new FakeFinalizer();
        $this->getContainer()->bindSingleton(FinalizerInterface::class, $finalizer);

        Assert::count($finalizer->calls, 0);

        $this->fakeHttp()->get('/get/query-params');

        Assert::count($finalizer->calls, 1);
        Assert::false($finalizer->calls[0]['terminate']);
    }

    #[Test]
    public function testFinalizersAreCalledAfterEachRequest(): void
    {
        $finalizer = new FakeFinalizer();
        $this->getContainer()->bindSingleton(FinalizerInterface::class, $finalizer);

        $this->fakeHttp()->get('/get/query-params');
        $this->fakeHttp()->post('/post/json', ['foo' => 'bar']);
        $this->fakeHttp()->get('/get/headers');

        Assert::count($finalizer->calls, 3);
        foreach ($finalizer->calls as $call) {
            Assert::false($call['terminate']);
        }
    }
}
