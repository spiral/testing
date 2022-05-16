<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Repositories;

final class ArrayPostRepository implements PostRepositoryInterface
{
    private array $posts;

    public function __construct(array $posts)
    {
        $this->posts = $posts;
    }

    public function all(): array
    {
        return $this->posts;
    }

    public function findById(int $id): ?array
    {
        return $this->posts[$id] ?? null;
    }
}
