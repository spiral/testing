<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use PHPUnit\Framework\ExpectationFailedException;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Internal\Introspector;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Tests\App\Middleware\FailMiddleware;
use Spiral\Testing\Tests\Http\Stub\FakeFinalizer;
use Spiral\Testing\Tests\Http\Stub\StaticResultMiddleware;
use Spiral\Testing\Tests\TestCase;

final class FakeHttpTest extends TestCase
{
    public function testGetBodySame(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[]');
    }

    public function testWithMiddleware(): void
    {
        $response = $this->fakeHttp()
            ->withMiddleware(StaticResultMiddleware::class)
            ->get('/get/query-params');
        $response->assertBodySame(StaticResultMiddleware::STATIC_RESULT);
    }

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
    public function testHttpScopeDoesNotConflict(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[]');
    }

    public function testAutoHttpScope(): void
    {
        $response = $this->fakeHttp()->get('/get/scopes');
        $response->assertBodySame('["http-request","http","root"]');
    }

    public function testGetWithQueryParams(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params', ['foo' => 'bar', 'baz' => ['foo1' => 'bar1']]);
        $response->assertBodySame('{"foo":"bar","baz":{"foo1":"bar1"}}');
    }

    public function testGetShouldThrowAnExceptionWhenNotSame(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Response is not same with [[foo]]');

        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[foo]');
    }

    public function testGetWithHeaders(): void
    {
        $response = $this->fakeHttp()->get('/get/headers', headers: ['foo' => 'bar', 'baz=bar']);
        $response->assertBodySame('{"foo":["bar"],"0":["baz=bar"]}');
    }

    public function testGetWithDefaultHeaders(): void
    {
        $http = $this->fakeHttp();
        $http->withHeaders(['baz' => 'bar']);
        $http->withHeader('foo', 'bar');

        $response = $http->get('/get/headers');
        $response->assertBodySame('{"baz":["bar"],"foo":["bar"]}');
    }

    public function testGetJsonParsedBody(): void
    {
        $http = $this->fakeHttp();
        $arr = [
            'foo' => 'bar',
            'list' => [1, 2, 3, 4],
        ];
        $response = $http->get('/get/query-params', $arr);
        self::assertSame(
            $arr,
            $response->getJsonParsedBody(),
        );
    }

    #[TestScope('foo')]
    public function testGetWithQueryParamsInScope(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params', ['foo' => 'bar', 'baz' => ['foo1' => 'bar1']]);
        $response->assertBodySame('{"foo":"bar","baz":{"foo1":"bar1"}}');
        $this->assertSame(['foo', 'root'], Introspector::scopeNames($this->getContainer()));
    }

    #[TestScope(['foo', 'bar'])]
    public function testGetWithQueryParamsInNestedScope(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params', ['foo' => 'bar', 'baz' => ['foo1' => 'bar1']]);
        $response->assertBodySame('{"foo":"bar","baz":{"foo1":"bar1"}}');
        $this->assertSame(['bar', 'foo', 'root'], Introspector::scopeNames($this->getContainer()));
    }

    public function testFinalizersAreCalledAfterRequest(): void
    {
        $finalizer = new FakeFinalizer();
        $this->getContainer()->bindSingleton(FinalizerInterface::class, $finalizer);

        $this->assertCount(0, $finalizer->calls);

        $this->fakeHttp()->get('/get/query-params');

        $this->assertCount(1, $finalizer->calls);
        $this->assertFalse($finalizer->calls[0]['terminate']);
    }

    public function testFinalizersAreCalledAfterEachRequest(): void
    {
        $finalizer = new FakeFinalizer();
        $this->getContainer()->bindSingleton(FinalizerInterface::class, $finalizer);

        $this->fakeHttp()->get('/get/query-params');
        $this->fakeHttp()->post('/post/json', ['foo' => 'bar']);
        $this->fakeHttp()->get('/get/headers');

        $this->assertCount(3, $finalizer->calls);
        foreach ($finalizer->calls as $call) {
            $this->assertFalse($call['terminate']);
        }
    }
}
