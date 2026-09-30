<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Testing\Events\FakeEventDispatcher;
use Spiral\Testing\Http\FakeHttp;
use Spiral\Testing\Tests\App\Event\AnotherEvent;
use Spiral\Testing\Tests\App\Event\SomeEvent;
use Spiral\Testing\Tests\App\Listener\AnotherListener;
use Spiral\Testing\Tests\App\Listener\SomeListener;
use Spiral\Testing\Tests\App\Listener\YetSomeListener;
use Spiral\Testing\Tests\TestCase;
use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Expect;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

final class EventDispatcherTest extends TestCase
{
    private FakeHttp $http;
    private FakeEventDispatcher $eventDispatcher;

    #[Test]
    public function testWithoutDispatcher(): void
    {
        $dispatcher = new FakeEventDispatcher();

        $event = $dispatcher->dispatch(new \stdClass());

        Assert::null($event);
    }

    #[Test]
    public function testWithoutEvents(): void
    {
        Expect::exception(AssertionException::class)->withMessageContaining('The expected [Spiral\Testing\Tests\App\Event\SomeEvent] event was not dispatched.');

        $this->eventDispatcher->assertDispatched(SomeEvent::class);
    }

    #[Test]
    public function testAssertNothingDispatched(): void
    {
        $this->eventDispatcher->assertNothingDispatched();
    }

    #[Test]
    public function testAssertNotDispatchedSomeEvent(): void
    {
        $this->eventDispatcher->assertNotDispatched(SomeEvent::class);
        $this->eventDispatcher->assertNotDispatched(AnotherEvent::class);
    }

    #[Test]
    public function testAssertDispatchedSomeEvent(): void
    {
        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertDispatched(SomeEvent::class);
        $this->eventDispatcher->assertDispatched(SomeEvent::class, fn(SomeEvent $event) => $event->someParam === 100);

        $this->eventDispatcher->assertNotDispatched(SomeEvent::class, fn(SomeEvent $event) => $event->someParam === 200);
        $this->eventDispatcher->assertNotDispatched(AnotherEvent::class);
    }

    #[Test]
    public function testAssertNotDispatchedSomeEventShouldThrowAnException(): void
    {
        Expect::exception(AssertionException::class)->withMessageContaining('The unexpected [Spiral\Testing\Tests\App\Event\SomeEvent] event was dispatched.');

        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertNotDispatched(SomeEvent::class, fn(SomeEvent $event) => $event->someParam === 100);
    }

    #[Test]
    public function testAssertNothingDispatchedShouldThrowAnException(): void
    {
        Expect::exception(AssertionException::class)->withMessageMatchingRegex('/\d+ unexpected events were dispatched./');

        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertNothingDispatched();
    }

    #[Test]
    public function testGetDispatchedEvents(): void
    {
        $this->http->get('/dispatch/some');

        $events = $this->eventDispatcher->dispatched(SomeEvent::class);

        Assert::count($events, 1);
        Assert::same($events[0]->someParam, 100);
    }

    #[Test]
    public function testAssertListeningSomeEvent(): void
    {
        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertListening(SomeEvent::class, SomeListener::class);
        $this->eventDispatcher->assertListening(SomeEvent::class, YetSomeListener::class);
    }

    #[Test]
    public function testAssertListeningShouldThrowAnException(): void
    {
        Expect::exception(AssertionException::class)->withMessageContaining('Event [Spiral\Testing\Tests\App\Event\SomeEvent] does not have the [Spiral\Testing\Tests\App\Listener\AnotherListener] listener attached to it.');

        $this->eventDispatcher->assertListening(SomeEvent::class, AnotherListener::class);
    }

    #[Test]
    public function testDecorate(): void
    {
        $inner = new class implements EventDispatcherInterface {
            public array $traces = [];

            public function dispatch(object $event): void
            {
                $this->traces[] = $event;
            }
        };
        $this->getContainer()->bindSingleton(EventDispatcherInterface::class, $inner);

        $eventDispatcher = $this->fakeEventDispatcher(decorate: true);
        $eventDispatcher->dispatch(new SomeEvent(2025));
        $eventDispatcher->dispatch(new AnotherEvent('boo'));
        $eventDispatcher->assertDispatched(SomeEvent::class);
        $eventDispatcher->assertDispatched(AnotherEvent::class);

        Assert::count($inner->traces, 2);
        Assert::instanceOf($inner->traces[0], SomeEvent::class);
        Assert::instanceOf($inner->traces[1], AnotherEvent::class);
    }

    #[Test]
    public function testClear(): void
    {
        $eventDispatcher = $this->fakeEventDispatcher();
        $eventDispatcher->dispatch(new SomeEvent(2025));
        $eventDispatcher->dispatch(new AnotherEvent('boo'));
        $eventDispatcher->assertDispatched(SomeEvent::class);
        $eventDispatcher->assertDispatched(AnotherEvent::class);
        $eventDispatcher->clear();

        $eventDispatcher->assertNotDispatched(SomeEvent::class);
        $eventDispatcher->assertNotDispatched(AnotherEvent::class);
    }

    #[BeforeTest]
    protected function prepare(): void
    {
        $this->eventDispatcher = $this->fakeEventDispatcher();
        $this->http = $this->fakeHttp();
    }
}
