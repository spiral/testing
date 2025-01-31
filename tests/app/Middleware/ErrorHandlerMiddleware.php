<?php

namespace Spiral\Testing\Tests\App\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Router\Exception\RouteNotFoundException;

final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RouteNotFoundException $e) {
            return new Response(404, body: \json_encode(['message' => $e->getMessage()]));
        } catch (\Throwable $e) {
            return new Response(500, body: \json_encode(['message' => $e->getMessage()]));
        }
    }
}
