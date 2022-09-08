<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Router\Annotation\Route;

class StatusesController
{
    #[Route('/status/200')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(200);
    }

    #[Route('/status/201')]
    public function created(): ResponseInterface
    {
        return new Response(201);
    }

    #[Route('/status/202')]
    public function accepted(): ResponseInterface
    {
        return new Response(202);
    }

    #[Route('/status/204')]
    public function noContent(): ResponseInterface
    {
        return new Response(204);
    }

    #[Route('/status/404')]
    public function notFound(): ResponseInterface
    {
        return new Response(404);
    }

    #[Route('/status/403')]
    public function forbidden(): ResponseInterface
    {
        return new Response(403);
    }

    #[Route('/status/401')]
    public function unauthorized(): ResponseInterface
    {
        return new Response(401);
    }

    #[Route('/status/422')]
    public function unprocessable(): ResponseInterface
    {
        return new Response(422);
    }
}
