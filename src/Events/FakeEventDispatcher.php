<?php

declare(strict_types=1);

namespace Spiral\Testing\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Testo\Assert;
use Testo\Common\Attribute\AssertMethod;

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
    ) {}

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
    #[AssertMethod]
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
                Assert::true(true);

                return;
            }
        }

        Assert::true(
            false,
            \sprintf(
                'Event [%s] does not have the [%s] listener attached to it.',
                $expectedEvent::class,
                $expectedListener,
            ),
        );
    }

    /**
     * Assert if an event was dispatched based on a truth-test callback.
     * @param class-string $event
     */
    #[AssertMethod]
    public function assertDispatched(string $event, ?\Closure $callback = null): void
    {
        Assert::true(
            \count($this->dispatched($event, $callback)) > 0,
            "The expected [{$event}] event was not dispatched.",
        );
    }

    /**
     * Assert if an event was dispatched a number of times.
     *
     * @param class-string $event
     * @param positive-int $times
     */
    #[AssertMethod]
    public function assertDispatchedTimes(string $event, int $times = 1): void
    {
        $count = \count($this->dispatched($event));

        Assert::same(
            $count,
            $times,
            "The expected [{$event}] event was dispatched {$count} times instead of {$times} times.",
        );
    }

    /**
     * Determine if an event was not dispatched based on a truth-test callback.
     *
     * @param class-string $event
     */
    #[AssertMethod]
    public function assertNotDispatched(string $event, ?\Closure $callback = null): void
    {
        Assert::same(
            \count($this->dispatched($event, $callback)),
            0,
            "The unexpected [{$event}] event was dispatched.",
        );
    }

    /**
     * Assert that no events were dispatched.
     */
    #[AssertMethod]
    public function assertNothingDispatched(): void
    {
        $count = count($this->dispatchedEvents);

        Assert::same(
            $count,
            0,
            "{$count} unexpected events were dispatched.",
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
            static fn(object $event): bool => $callback($event),
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

    public function clear(): void
    {
        $this->dispatchedEvents = [];
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
