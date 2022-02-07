<?php

declare(strict_types=1);

namespace Spiral\Testing\Auth;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenInterface;

final class FakeActorProvider implements ActorProviderInterface
{
    private object $actor;

    public function __construct(object $actor)
    {
        $this->actor = $actor;
    }

    public function getActor(TokenInterface $token): ?object
    {
        return $this->actor;
    }
}
