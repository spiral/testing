<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Spiral\Auth\AuthContextInterface;
use Spiral\Router\Annotation\Route;

final class AuthController
{
    public function __construct(
        private readonly AuthContextInterface $auth,
    ) {}

    #[Route(route: '/auth/actor', methods: 'GET')]
    public function actor(): array
    {
        $actor = $this->auth->getActor();

        return $actor === null ? [] : (array) $actor;
    }
}
