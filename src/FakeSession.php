<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Spiral\Session\Session;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionSection;
use Spiral\Session\SessionSectionInterface;

final class FakeSession implements SessionInterface
{
    public function __construct(private array $data)
    {
    }

    public function isStarted(): bool
    {
        return true;
    }

    public function resume(): void
    {
    }

    public function getID(): ?string
    {
        return 'session-id';
    }

    public function regenerateID(): SessionInterface
    {
        return $this;
    }

    public function commit(): bool
    {
        return true;
    }

    public function abort(): bool
    {
        return true;
    }

    public function destroy(): bool
    {
        return true;
    }

    public function getSection(string $name = null): SessionSectionInterface
    {
        return new SessionSection($this, $name ?? '__DEFAULT__');
    }
}
