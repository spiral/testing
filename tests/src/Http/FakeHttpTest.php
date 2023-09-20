<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use PHPUnit\Framework\ExpectationFailedException;
use Spiral\Testing\Tests\TestCase;

final class FakeHttpTest extends TestCase
{
    public function testGetBodySame(): void
    {
        $response = $this->fakeHttp()->get('/get/query-params');
        $response->assertBodySame('[]');
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
            'list' => [1, 2, 3, 4]
        ];
        $response = $http->get('/get/query-params', $arr);
        self::assertSame(
            $arr,
            $response->getJsonParsedBody()
        );
    }
}
