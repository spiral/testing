<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http\Stub;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\Middleware\AuthMiddleware;

final class ActorReportingMiddleware implements MiddlewareInterface
{
    public const NO_ACTOR = 'guest';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = $request->getAttribute(AuthMiddleware::ATTRIBUTE);
        $actor = $context instanceof AuthContextInterface ? $context->getActor() : null;

        return new Response(200, body: $actor === null ? self::NO_ACTOR : $actor::class);
    }
}
