<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Router\Annotation\Route;
use Spiral\Testing\Tests\App\Event\AnotherEvent;
use Spiral\Testing\Tests\App\Event\SomeEvent;

class EventDispatcherController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/dispatch/some', 'dispatch.some')]
    public function dispatchSome(): string
    {
        $this->eventDispatcher->dispatch(new SomeEvent(100));

        return 'ok';
    }

    #[Route('/dispatch/another', 'dispatch.another')]
    public function dispatchAnother(): string
    {
        $this->eventDispatcher->dispatch(new AnotherEvent('foo'));

        return 'ok';
    }
}
