<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;

final class FakeTokenStorage implements TokenStorageInterface
{
    public function load(string $id): ?TokenInterface
    {
        return new Token($id, []);
    }

    public function create(array $payload, \DateTimeInterface $expiresAt = null): TokenInterface
    {
        return new Token(uniqid(), $payload, $expiresAt);
    }

    public function delete(TokenInterface $token): void
    {
    }
}
