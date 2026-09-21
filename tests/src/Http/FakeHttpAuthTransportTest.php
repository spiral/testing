<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\Middleware\AuthTransportMiddleware;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\Container\Autowire;
use Spiral\Testing\Tests\App\Bootloader\CustomAuthTransportBootloader;
use Spiral\Testing\Tests\Http\Stub\ActorReportingMiddleware;
use Spiral\Testing\Tests\TestCase;

final class FakeHttpAuthTransportTest extends TestCase
{
    private const HEADER_AUTH = 'auth.header';
    private const CUSTOM_AUTH = 'auth.custom';

    public function defineBootloaders(): array
    {
        return [...parent::defineBootloaders(), CustomAuthTransportBootloader::class];
    }

    public function testCustomTransportIsConfigured(): void
    {
        $transports = $this->getContainer()->get(TransportRegistry::class)->getTransports();

        $this->assertArrayHasKey(CustomAuthTransportBootloader::TRANSPORT, $transports);
    }

    public function testActorIsAuthenticatedThroughNamedTransport(): void
    {
        $actor = new \stdClass();

        $response = $this->fakeHttp()
            ->withActor($actor)
            ->withMiddleware(ActorReportingMiddleware::class, self::HEADER_AUTH)
            ->get('/get/query-params');

        $response->assertOk();
        $response->assertBodySame($actor::class);
    }

    public function testActorIsAuthenticatedThroughCustomTransport(): void
    {
        $actor = new \stdClass();

        $response = $this->fakeHttp()
            ->withActor($actor)
            ->withMiddleware(ActorReportingMiddleware::class, self::CUSTOM_AUTH)
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
            ->withMiddleware(ActorReportingMiddleware::class, self::CUSTOM_AUTH)
            ->get('/get/query-params');

        $response->assertOk();
        $response->assertBodySame(ActorReportingMiddleware::NO_ACTOR);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $binder = $this->getContainer()->getBinder('http');
        $binder->bind(
            self::HEADER_AUTH,
            new Autowire(AuthTransportMiddleware::class, ['transportName' => 'header']),
        );
        $binder->bind(
            self::CUSTOM_AUTH,
            new Autowire(AuthTransportMiddleware::class, ['transportName' => CustomAuthTransportBootloader::TRANSPORT]),
        );
    }
}
