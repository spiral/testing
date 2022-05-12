<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\Container;
use Spiral\Http\Http;
use Spiral\Security\ActorInterface;
use Spiral\Session\SessionInterface;
use Spiral\Testing\Auth\FakeActorProvider;
use Spiral\Testing\Session\FakeSession;

class FakeHttp
{
    private const AUTH_TOKEN_HEADER_KEY = 'X-Test-Token';

    private array $defaultServerVariables = [];
    private array $defaultHeaders = [];
    private array $defaultCookies = [];

    private ?object $actor = null;
    private ?SessionInterface $session = null;
    private Container $container;
    private \Closure $scope;
    private FileFactory $fileFactory;

    public function __construct(
        Container $container,
        FileFactory $fileFactory,
        \Closure $scope
    ) {
        $this->container = $container;
        $this->scope = $scope;
        $this->fileFactory = $fileFactory;
    }

    public function withActor(object $actor): self
    {
        $this->actor = $actor;

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

    public function getFileFactory(): FileFactory
    {
        return $this->fileFactory;
    }

    public function getHttp(): Http
    {
        return $this->container->get(Http::class);
    }

    public function get(string $uri, array $query = [], array $headers = [], array $cookies = []): TestResponse
    {
        return $this->handleRequest(
            $this->createRequest($uri, 'GET', $query, $headers, $cookies)
        );
    }

    public function getJson(
        string $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'GET', $query, $headers, $cookies)
        );
    }

    public function getWithAttributes(string $uri, array $attributes, array $headers = []): TestResponse
    {
        $r = $this->createRequest($uri, 'GET', [], $headers, []);
        foreach ($attributes as $k => $v) {
            $r = $r->withAttribute($k, $v);
        }

        return $this->handleRequest($r);
    }

    public function post(
        string $uri,
        array $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createRequest($uri, 'POST', [], $headers, $cookies, $files)->withParsedBody($data)
        );
    }

    public function postJson(
        string $uri,
        array $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'POST', $data, $headers, $cookies, $files)
        );
    }

    public function put(
        string $uri,
        array $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createRequest($uri, 'PUT', $data, $headers, $cookies, $files)
        );
    }

    public function putJson(
        string $uri,
        array $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'PUT', $data, $headers, $cookies, $files)
        );
    }

    public function delete(
        string $uri,
        array $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createRequest($uri, 'DELETE', $data, $headers, $cookies, $files)
        );
    }

    public function deleteJson(
        string $uri,
        array $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = []
    ): TestResponse {
        return $this->handleRequest(
            $this->createJsonRequest($uri, 'DELETE', $data, $headers, $cookies, $files)
        );
    }

    protected function createJsonRequest(
        string $uri,
        string $method,
        array $data,
        array $headers,
        array $cookies,
        array $files = []
    ): ServerRequest {
        $content = \json_encode($data);

        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);

        return $this->createRequest($uri, $method, [], $headers, $cookies, $files)
            ->withBody(new Stream($content));
    }

    /**
     * @param array<UploadedFileInterface> $files
     */
    protected function createRequest(
        string $uri,
        string $method,
        array $query,
        array $headers,
        array $cookies,
        array $files = []
    ): ServerRequest {
        $cookies = \array_merge($this->defaultCookies, $cookies);
        $headers = \array_merge($this->defaultHeaders, $headers);

        return new ServerRequest(
            $this->defaultServerVariables,
            $files,
            $uri,
            $method,
            'php://input',
            $headers,
            $cookies,
            $query
        );
    }

    protected function handleRequest(ServerRequestInterface $request, array $bindings = []): TestResponse
    {
        if ($this->actor) {
            $request = $request->withHeader(static::AUTH_TOKEN_HEADER_KEY, spl_object_hash($this->actor));

            $bindings[ActorProviderInterface::class] = new FakeActorProvider($this->actor);
            $bindings[TokenStorageInterface::class] = new FakeTokenStorage();

            $transport = new TransportRegistry();
            $transport->setTransport('testing', new HeaderTransport(static::AUTH_TOKEN_HEADER_KEY));
            $bindings[TransportRegistry::class] = $transport;
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
