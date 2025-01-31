<?php

namespace Spiral\Testing\Tests\App\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Attribute\Scope;

#[Scope('http')]
final class FailMiddleware implements MiddlewareInterface
{
    public const STATIC_RESULT = "['result' => 'fail']";
    public const ROUTE = '/fail-middleware';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $request->getUri()->getPath() === self::ROUTE
            ? new Response(500, [], self::STATIC_RESULT)
            : $handler->handle($request);
    }
}
