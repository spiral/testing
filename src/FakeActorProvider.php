<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenInterface;

final class FakeActorProvider implements ActorProviderInterface
{
    public function __construct(private object $actor)
    {
    }

    public function getActor(TokenInterface $token): ?object
    {
        return $this->actor;
    }
}
