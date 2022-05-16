<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Spiral\Http\Request\InputManager;
use Spiral\Testing\Tests\App\Repositories\PostRepositoryInterface;

final class PostController
{
    public function all(PostRepositoryInterface $posts): array
    {
        return $posts->all();
    }

    public function show(int $id, PostRepositoryInterface $posts): ?array
    {
        return $posts->findById($id);
    }
}
