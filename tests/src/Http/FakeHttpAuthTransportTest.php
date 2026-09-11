<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Testing\Tests\Http\Stub\ActorReportingMiddleware;
use Spiral\Testing\Tests\Http\Stub\HeaderAuthMiddleware;
use Spiral\Testing\Tests\TestCase;

final class FakeHttpAuthTransportTest extends TestCase
{
    public function defineBootloaders(): array
    {
        return [...parent::defineBootloaders(), HttpAuthBootloader::class];
    }

    public function testActorIsAuthenticatedThroughNamedTransport(): void
    {
        $actor = new \stdClass();

        $response = $this->fakeHttp()
            ->withActor($actor)
            ->withMiddleware(ActorReportingMiddleware::class, HeaderAuthMiddleware::class)
            ->get('/get/query-params');

        $response->assertOk();
        $response->assertBodySame($actor::class);
    }

    public function testActorIsAuthenticatedThroughEveryTransport(): void
    {
        $actor = new \stdClass();

        $response = $this->fakeHttp()
            ->withActor($actor)
            ->withMiddleware(ActorReportingMiddleware::class, AuthMiddleware::class)
            ->get('/get/query-params');

        $response->assertOk();
        $response->assertBodySame($actor::class);
    }

    public function testRequestWithoutActorStaysUnauthenticated(): void
    {
        $response = $this->fakeHttp()
            ->withMiddleware(ActorReportingMiddleware::class, HeaderAuthMiddleware::class)
            ->get('/get/query-params');

        $response->assertOk();
        $response->assertBodySame(ActorReportingMiddleware::NO_ACTOR);
    }
}
