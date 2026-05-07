<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Spiral\Auth\AuthContextInterface;
use Spiral\Router\Annotation\Route;

class AuthController
{
    public function __construct(
        protected AuthContextInterface $auth,
    ) {}

    #[Route(route: '/some-method', methods: 'GET')]
    public function someMethod(): array
    {
        $actor = $this->auth->getActor();

        return $actor === null ? [] : (array) $actor;
    }
}
