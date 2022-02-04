<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\AuthContext;
use Spiral\Auth\AuthContextInterface;
use Spiral\Core\Container;
use Spiral\Http\Http;
use Spiral\Session\SessionInterface;
use Spiral\Testing\Auth\FakeActorProvider;
use Spiral\Testing\Session\FakeSession;

class FakeHttp
{
    private array $defaultServerVariables = [];
    private array $defaultHeaders = [];
    private array $defaultCookies = [];

    private ?AuthContextInterface $auth = null;
    private ?SessionInterface $session = null;

    public function __construct(
        private Container $container,
        private \Closure $scope
    ) {
    }

    public function withActor(object $actor): self
    {
        $this->auth = new AuthContext(new FakeActorProvider($actor));

        return $this;
    }

    public function withServerVariables(array $variables): self
    {
        $this->defaultServerVariables = $variables;

        return $this;
    }

    public function flushServerVariables(): self
    {
        $this->defaultServerVariables = [];

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->defaultHeaders = $headers;

        return $this;
    }

    public function withHeader(string $key, $value): self
    {
        $this->defaultHeaders[$key] = $value;

        return $this;
    }

    public function withAuthorizationToken(string $token, string $type = 'Bearer'): self
    {
        return $this->withHeader('Authorization', $type.' '.$token);
    }

    public function flushHeaders(): self
    {
        $this->defaultHeaders = [];

        return $this;
    }

    public function withCookies(array $cookies): self
    {
        $this->defaultCookies = $cookies;

        return $this;
    }

    public function withCookie(string $name, string $value): self
    {
        $this->defaultCookies[$name] = $value;

        return $this;
    }

    public function flushCookies(): self
    {
        $this->defaultCookies = [];

        return $this;
    }

    public function withSession(
        array $data,
        string $clientSignature = 'fake-session',
        int $lifetime = 3600,
        ?string $id = null
    ): self {
        $this->session = new FakeSession($data, $clientSignature, $lifetime, $id);

        return $this;
    }

    public function flushSession(): self
    {
        $this->session = null;

        return $this;
    }

    public function withMiddleware(string ...$middleware): self
    {
        foreach ($middleware as $name) {
            $this->container->removeBinding($name);
        }

        return $this;
    }

    public function withoutMiddleware(string ...$middleware): self
    {
        foreach ($middleware as $name) {
            $this->container->bindSingleton(
                $name,
                new class implements MiddlewareInterface {
                    public function process(
                        ServerRequestInterface $request,
                        RequestHandlerInterface $handler
                    ): ResponseInterface {
                        return $handler->handle($request);
                    }
                }
            );
        }

        return $this;
    }

    public function getHttp(): Http
    {
        return $this->container->get(Http::class);
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

        return $this->createRequest($uri, $method, [], $headers, $cookies)
            ->withBody(new Stream($content));
    }

    protected function createRequest(
        string $uri,
        string $method,
        array $query,
        array $headers,
        array $cookies
    ): ServerRequest {
        $cookies = \array_merge($this->defaultCookies, $cookies);
        $headers = \array_merge($this->defaultHeaders, $headers);

        return new ServerRequest(
            serverParams: $this->defaultServerVariables,
            uri: $uri,
            method: $method,
            headers: $headers,
            cookies: $cookies,
            queryParams: $query
        );
    }

    protected function handleRequest(ServerRequestInterface $request, array $bindings = []): TestResponse
    {
        if ($this->auth) {
            $bindings[AuthContextInterface::class] = $this->auth;
        }

        if ($this->session) {
            $bindings[SessionInterface::class] = $this->session;
        }

        $handler = function () use ($request) {
            return $this->getHttp()->handle($request);
        };

        $scope = $this->scope;

        return new TestResponse($scope($handler, $bindings));
    }
}
