<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Router\Annotation\Route;

class JsonController
{
    #[Route('/stream/get', 'stream.get')]
    public function get(ServerRequestInterface $request): string
    {
        return (string) $request->getBody();
    }

    #[Route('/stream/post', 'stream.post')]
    public function post(ServerRequestInterface $request): string
    {
        return (string) $request->getBody();
    }

    #[Route('/stream/put', 'stream.put')]
    public function put(ServerRequestInterface $request): string
    {
        return (string) $request->getBody();
    }

    #[Route('/stream/delete', 'stream.delete')]
    public function delete(ServerRequestInterface $request): string
    {
        return (string) $request->getBody();
    }
}
