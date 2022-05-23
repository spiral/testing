<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use Spiral\Auth\TokenInterface;

class Token implements TokenInterface
{
    public function __construct(
        private readonly string $id,
        private readonly array $payload,
        private readonly ?\DateTimeInterface $expiresAt = null
    ) {
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
