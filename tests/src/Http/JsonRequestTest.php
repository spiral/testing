<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use Nyholm\Psr7\Stream;
use Spiral\Testing\Tests\TestCase;

final class JsonRequestTest extends TestCase
{
    public function testGet(): void
    {
        $this->fakeHttp()->getJson('/stream/get', ['foo' => 'bar'])
            ->assertBodySame('{"foo":"bar"}');
    }

    public function testPost(): void
    {
        $this->fakeHttp()->postJson('/stream/post', ['foo' => 'bar'])
            ->assertBodySame('{"foo":"bar"}');
    }

    public function testPostWithStream(): void
    {
        $this->fakeHttp()->postJson('/stream/post', Stream::create('{"foo":"bar"}'))
            ->assertBodySame('{"foo":"bar"}');
    }

    public function testPut(): void
    {
        $this->fakeHttp()->putJson('/stream/put', ['foo' => 'bar'])
            ->assertBodySame('{"foo":"bar"}');
    }

    public function testPutWithStream(): void
    {
        $this->fakeHttp()->putJson('/stream/put', Stream::create('{"foo":"bar"}'))
            ->assertBodySame('{"foo":"bar"}');
    }

    public function testDelete(): void
    {
        $this->fakeHttp()->deleteJson('/stream/delete', ['foo' => 'bar'])
            ->assertBodySame('{"foo":"bar"}');
    }

    public function testDeleteWithStream(): void
    {
        $this->fakeHttp()->deleteJson('/stream/delete', Stream::create('{"foo":"bar"}'))
            ->assertBodySame('{"foo":"bar"}');
    }
}
