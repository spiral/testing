<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\AuthContext;
use Spiral\Http\Http;
use Spiral\Testing\Auth\FakeActorProvider;
use Spiral\Testing\Http\TestResponse;
use Spiral\Testing\Session\FakeSession;

trait HttpAssertions
{
    public function withActor(object $actor): self
    {
        $this->auth = new AuthContext(new FakeActorProvider($actor));

        return $this;
    }

    public function withSession(array $data): self
    {
        $this->session = new FakeSession($data);
    }

    protected function getHttp(): RequestHandlerInterface
    {
        return $this->getContainer()->get(Http::class);
    }

    protected function get(string $uri, array $query = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createRequest($uri, 'GET', $query, $headers, $cookies)
        );
    }

    protected function getJson(
        string $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'GET', $query, $headers, $cookies)
        );
    }

    protected function getWithAttributes(string $uri, array $attributes, array $headers = []): TestResponse
    {
        $r = $this->createRequest($uri, 'GET', [], $headers, []);
        foreach ($attributes as $k => $v) {
            $r = $r->withAttribute($k, $v);
        }

        return $this->handleRequest($r);
    }

    protected function post(string $uri, array $data = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createRequest($uri, 'POST', [], $headers, $cookies)->withParsedBody($data)
        );
    }

    protected function postJson(string $uri, array $data = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'POST', $data, $headers, $cookies)
        );
    }

    protected function put(string $uri, array $data = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createRequest($uri, 'PUT', $data, $headers, $cookies)
        );
    }

    protected function putJson(string $uri, array $data = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'PUT', $data, $headers, $cookies)
        );
    }

    protected function delete(string $uri, array $data = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createRequest($uri, 'DELETE', $data, $headers, $cookies)
        );
    }

    protected function deleteJson(string $uri, array $data = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'DELETE', $data, $headers, $cookies)
        );
    }

    protected function handleRequest(ServerRequestInterface $request): TestResponse
    {
        $handler = function () use ($request) {
            return $this->getHttp()->handle($request);
        };

        return new TestResponse($this->runScoped($handler));
    }

    protected function createJsonRequest(
        string $uri,
        string $method,
        array $data,
        array $headers,
        array $cookies
    ): ServerRequest {
        $content = json_encode($data);

        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);

        return $this->createRequest($uri, $method, [], $headers, $cookies)->withBody(new Stream($content));
    }

    protected function createRequest(
        string $uri,
        string $method,
        array $query,
        array $headers,
        array $cookies
    ): ServerRequest {
        return new ServerRequest(
            uri: $uri,
            method: $method,
            headers: $headers,
            cookies: $cookies,
            queryParams: $query
        );
    }

    protected function fetchCookies(array $header): array
    {
        $result = [];
        foreach ($header as $line) {
            $cookie = explode('=', $line);
            $result[$cookie[0]] = rawurldecode(substr($cookie[1], 0, strpos($cookie[1], ';')));
        }

        return $result;
    }
}
