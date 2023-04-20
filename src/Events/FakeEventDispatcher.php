<?php

declare(strict_types=1);

namespace Spiral\Testing\Events;

use PHPUnit\Framework\Assert as PHPUnit;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class FakeEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<class-string, object[]>
     */
    private array $dispatchedEvents = [];

    /**
     * @param class-string[] $eventsToFake
     */
    public function __construct(
        private readonly array $eventsToFake = [],
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        private readonly ?ListenerProviderInterface $listenerProvider = null,
    ) {
    }

    public function dispatch(object $event): ?object
    {
        if ($this->shouldFakeEvent($event::class)) {
            if (! isset($this->dispatchedEvents[$event::class])) {
                $this->dispatchedEvents[$event::class] = [];
            }

            $this->dispatchedEvents[$event::class][] = $event;
        }

        return $this->eventDispatcher?->dispatch($event);
    }

    /**
     * Assert if an event has a listener attached to it.
     *
     * @param class-string $expectedEvent
     * @param class-string $expectedListener
     * @throws \ReflectionException
     */
    public function assertListening(string $expectedEvent, string $expectedListener): void
    {
        $expectedEvent = new \ReflectionClass($expectedEvent);
        $expectedEvent = $expectedEvent->newInstanceWithoutConstructor();

        $listeners = $this->listenerProvider?->getListenersForEvent($expectedEvent) ?? [];

        foreach ($listeners as $listenerClosure) {
            $actualListener = (new \ReflectionFunction($listenerClosure))
                ->getStaticVariables()['listener'];

            if (\is_object($actualListener)) {
                $actualListener = $actualListener::class;
            }

            if ($actualListener === $expectedListener) {
                PHPUnit::assertTrue(true);

                return;
            }
        }

        PHPUnit::assertTrue(
            false,
            \sprintf(
                'Event [%s] does not have the [%s] listener attached to it.',
                $expectedEvent::class,
                $expectedListener
            )
        );
    }

    /**
     * Assert if an event was dispatched based on a truth-test callback.
     * @param class-string $event
     */
    public function assertDispatched(string $event, ?\Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            \count($this->dispatched($event, $callback)) > 0,
            "The expected [{$event}] event was not dispatched."
        );
    }

    /**
     * Assert if an event was dispatched a number of times.
     *
     * @param class-string $event
     * @param positive-int $times
     */
    public function assertDispatchedTimes(string $event, int $times = 1): void
    {
        $count = \count($this->dispatched($event));

        PHPUnit::assertSame(
            $times,
            $count,
            "The expected [{$event}] event was dispatched {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if an event was not dispatched based on a truth-test callback.
     *
     * @param class-string $event
     */
    public function assertNotDispatched(string $event, ?\Closure $callback = null): void
    {
        PHPUnit::assertCount(
            0,
            $this->dispatched($event, $callback),
            "The unexpected [{$event}] event was dispatched."
        );
    }

    /**
     * Assert that no events were dispatched.
     */
    public function assertNothingDispatched(): void
    {
        $count = count($this->dispatchedEvents);

        PHPUnit::assertSame(
            0,
            $count,
            "{$count} unexpected events were dispatched."
        );
    }

    /**
     * Get all the events matching a truth-test callback.
     *
     * @param class-string $event
     * @return object[]
     */
    public function dispatched(string $event, ?\Closure $callback = null): array
    {
        if (! $this->hasDispatched($event)) {
            return [];
        }

        $callback = $callback ?: static fn(): bool => true;

        return \array_filter(
            $this->dispatchedEvents[$event],
            static fn(object $event): bool => $callback($event)
        );
    }

    /**
     * Determine if the given event has been dispatched.
     *
     * @param class-string $event
     */
    public function hasDispatched(string $event): bool
    {
        return isset($this->dispatchedEvents[$event]) && $this->dispatchedEvents[$event] !== [];
    }

    /**
     * @param class-string $event
     */
    private function shouldFakeEvent(string $event): bool
    {
        if ($this->eventsToFake === []) {
            return true;
        }

        if (\in_array($event, $this->eventsToFake, true)) {
            return true;
        }

        return false;
    }
}
