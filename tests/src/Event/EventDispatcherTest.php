<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Event;

use PHPUnit\Framework\ExpectationFailedException;
use Spiral\Testing\Events\FakeEventDispatcher;
use Spiral\Testing\Tests\App\Event\AnotherEvent;
use Spiral\Testing\Tests\App\Event\SomeEvent;
use Spiral\Testing\Tests\App\Listener\AnotherListener;
use Spiral\Testing\Tests\App\Listener\SomeListener;
use Spiral\Testing\Tests\App\Listener\YetSomeListener;
use Spiral\Testing\Tests\TestCase;

final class EventDispatcherTest extends TestCase
{
    private \Spiral\Testing\Http\FakeHttp $http;
    private \Spiral\Testing\Events\FakeEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = $this->fakeEventDispatcher();
        $this->http = $this->fakeHttp();
    }

    public function testWithoutDispatcher(): void
    {
        $dispatcher = new FakeEventDispatcher();

        $event = $dispatcher->dispatch(new \stdClass());

        $this->assertNull($event);
    }

    public function testWithoutEvents(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The expected [Spiral\Testing\Tests\App\Event\SomeEvent] event was not dispatched.');

        $this->eventDispatcher->assertDispatched(SomeEvent::class);
    }

    public function testAssertNothingDispatched(): void
    {
        $this->eventDispatcher->assertNothingDispatched();
    }

    public function testAssertNotDispatchedSomeEvent(): void
    {
        $this->eventDispatcher->assertNotDispatched(SomeEvent::class);
        $this->eventDispatcher->assertNotDispatched(AnotherEvent::class);
    }

    public function testAssertDispatchedSomeEvent(): void
    {
        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertDispatched(SomeEvent::class);
        $this->eventDispatcher->assertDispatched(SomeEvent::class, fn(SomeEvent $event) => $event->someParam === 100);

        $this->eventDispatcher->assertNotDispatched(SomeEvent::class, fn(SomeEvent $event) => $event->someParam === 200);
        $this->eventDispatcher->assertNotDispatched(AnotherEvent::class);
    }

    public function testAssertNotDispatchedSomeEventShouldThrowAnException(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The unexpected [Spiral\Testing\Tests\App\Event\SomeEvent] event was dispatched.');

        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertNotDispatched(SomeEvent::class, fn(SomeEvent $event) => $event->someParam === 100);
    }

    public function testAssertNothingDispatchedShouldThrowAnException(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('3 unexpected events were dispatched.');

        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertNothingDispatched();
    }

    public function testGetDispatchedEvents(): void
    {
        $this->http->get('/dispatch/some');

        $events = $this->eventDispatcher->dispatched(SomeEvent::class);

        $this->assertCount(1, $events);
        $this->assertSame(100, $events[0]->someParam);
    }

    public function testAssertListeningSomeEvent(): void
    {
        $this->http->get('/dispatch/some');

        $this->eventDispatcher->assertListening(SomeEvent::class, SomeListener::class);
        $this->eventDispatcher->assertListening(SomeEvent::class, YetSomeListener::class);
    }

    public function testAssertListeningShouldThrowAnException(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Event [Spiral\Testing\Tests\App\Event\SomeEvent] does not have the [Spiral\Testing\Tests\App\Listener\AnotherListener] listener attached to it.');

        $this->eventDispatcher->assertListening(SomeEvent::class, AnotherListener::class);
    }
}
