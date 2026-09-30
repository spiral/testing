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
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

final class FakeHttpAuthTransportTest extends TestCase
{
    private const HEADER_AUTH = 'auth.header';
    private const CUSTOM_AUTH = 'auth.custom';

    public function defineBootloaders(): array
    {
        return [...parent::defineBootloaders(), CustomAuthTransportBootloader::class];
    }

    #[Test]
    public function testCustomTransportIsConfigured(): void
    {
        $transports = $this->getContainer()->get(TransportRegistry::class)->getTransports();

        Assert::array($transports)->hasKeys(CustomAuthTransportBootloader::TRANSPORT);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function testRequestWithoutActorStaysUnauthenticated(): void
    {
        $response = $this->fakeHttp()
            ->withMiddleware(ActorReportingMiddleware::class, self::CUSTOM_AUTH)
            ->get('/get/query-params');

        $response->assertOk();
        $response->assertBodySame(ActorReportingMiddleware::NO_ACTOR);
    }

    #[BeforeTest]
    protected function prepare(): void
    {
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
