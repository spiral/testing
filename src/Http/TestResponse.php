<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class TestResponse
{
    public function __construct(private ResponseInterface $response)
    {
    }

    public function getStatusCode(): int
    {
        return $this->getStatusCode();
    }

    public function assertHasHeader(string $name, ?string $value = null): self
    {
        TestCase::assertTrue(
            $this->response->hasHeader($name),
            \sprintf('Response does not contain header with name [%s].', $name)
        );

        $headerValue = $this->response->getHeaderLine($name);

        if ($value) {
            TestCase::assertSame(
                $value,
                $headerValue,
                \sprintf("Header [%s] was found, but value [%s] does not match [%s].", $name, $headerValue, $value)
            );
        }

        return $this;
    }

    public function assertHeaderMissing(string $name): self
    {
        TestCase::assertFalse(
            $this->response->hasHeader($name),
            \sprintf('Response contains header with name [%s].', $name)
        );
    }

    public function assertStatus(int $status): self
    {
        TestCase::assertSame(
            $this->response->getStatusCode(),
            $status,
            \sprintf(
                "Received response status code [%s : %s] but expected %s.",
                $this->response->getStatusCode(),
                $this->response->getReasonPhrase(),
                $status
            )
        );

        return $this;
    }

    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }

    public function assertCreated(): self
    {
        return $this->assertStatus(201);
    }

    public function assertAccepted(): self
    {
        return $this->assertStatus(202);
    }

    public function assertNoContent(int $status = 204): self
    {
        $this->assertStatus(202);

        TestCase::assertEmpty(
            $this->response->getBody()->getContents(),
            'Response content should be empty.'
        );

        return $this;
    }

    public function assertNotFound(): self
    {
        return $this->assertStatus(404);
    }

    public function assertForbidden(): self
    {
        return $this->assertStatus(403);
    }

    public function assertUnauthorized(): self
    {
        return $this->assertStatus(401);
    }

    public function assertUnprocessable(): self
    {
        return $this->assertStatus(422);
    }

    public function isRedirect(): bool
    {
        return \in_array($this->response->getStatusCode(), [201, 301, 302, 303, 307, 308]);
    }
}
