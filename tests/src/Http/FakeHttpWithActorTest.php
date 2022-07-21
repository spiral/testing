<?php

namespace Spiral\Testing\Tests\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\AuthScope;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Http\Http;
use Spiral\Testing\Http\FakeTokenStorage;
use Spiral\Testing\Tests\TestCase;
use function spiral;

class FakeHttpWithActorTest extends TestCase
{
    public function defineBootloaders(): array
    {
        /** @psalm-suppress DuplicateArrayKey */
        return [
            ...parent::defineBootloaders(),
            HttpAuthBootloader::class
        ];
    }

    public function testWithActor()
    {
        $this->assertBootloaderRegistered(HttpAuthBootloader::class);

        $actor = new \stdClass();
        $actor->id = $actorId = 777;

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /** @var AuthScope $authScope */
                $authScope = spiral(AuthScope::class);
                $actor = $authScope->getActor();

                if ($actor === null) {
                    $body = "null";
                } else {
                    $body = $actor->id;
                }
                return new Response(200, [], $body);
            }
        };

        $this->getContainer()->bind(TokenStorageInterface::class, FakeTokenStorage::class);
        $this->getContainer()->get(Http::class)
            ->setHandler($handler);

        $this->fakeHttp()
            ->get('/')
            ->assertBodyContains("null");

        $this->fakeHttp()
            ->withActor($actor)
            ->get('/')
            ->assertBodyContains((string)$actorId);
    }
}
