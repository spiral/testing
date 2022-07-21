<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http;

use Spiral\Testing\Tests\TestCase;

final class ResponseStatusesTest extends TestCase
{
    public function testGetOk(): void
    {
        $response = $this->fakeHttp()->get('/status/200');
        $response->assertOk();
        $response->assertStatus(200);
    }

    public function testGetWithStatusCreated(): void
    {
        $response = $this->fakeHttp()->get('/status/201');
        $response->assertCreated();
        $response->assertStatus(201);
    }

    public function testGetWithStatusAccepted(): void
    {
        $response = $this->fakeHttp()->get('/status/202');
        $response->assertAccepted();
        $response->assertStatus(202);
    }

    public function testGetWithStatusNotFound(): void
    {
        $response = $this->fakeHttp()->get('/status/404');
        $response->assertNotFound();
        $response->assertStatus(404);
    }

    public function testGetWithStatusForbidden(): void
    {
        $response = $this->fakeHttp()->get('/status/403');
        $response->assertForbidden();
        $response->assertStatus(403);
    }

    public function testGetWithStatusUnauthorized(): void
    {
        $response = $this->fakeHttp()->get('/status/401');
        $response->assertUnauthorized();
        $response->assertStatus(401);
    }

    public function testGetWithStatusUnprocessable(): void
    {
        $response = $this->fakeHttp()->get('/status/422');
        $response->assertUnprocessable();
        $response->assertStatus(422);
    }
}
