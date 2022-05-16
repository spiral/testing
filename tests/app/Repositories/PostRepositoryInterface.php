<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Repositories;

interface PostRepositoryInterface
{
    public function all(): array;
    public function findById(int $id): ?array;
}
