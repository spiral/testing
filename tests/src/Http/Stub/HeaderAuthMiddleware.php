<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http\Stub;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\Middleware\AuthTransportMiddleware;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\ScopeInterface;

final class HeaderAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        #[Proxy] private readonly ContainerInterface $container,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = new AuthTransportMiddleware(
            'header',
            $this->container->get(ScopeInterface::class),
            $this->container->get(ActorProviderInterface::class),
            $this->container->get(TokenStorageInterface::class),
            $this->container->get(TransportRegistry::class),
        );

        return $middleware->process($request, $handler);
    }
}
