<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use Psr\Http\Message\ResponseInterface;
use Testo\Assert;
use Testo\Common\Attribute\AssertMethod;

final class TestResponse implements \Stringable
{
    private array $cookies;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        $this->cookies = $this->fetchCookies($this->response->getHeader('Set-Cookie'));
    }

    /**
     * Get response status code
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    #[AssertMethod]
    public function assertHasHeader(string $name, ?string $value = null): self
    {
        Assert::true(
            $this->response->hasHeader($name),
            \sprintf('Response does not contain header with name [%s].', $name),
        );

        $headerValue = $this->response->getHeaderLine($name);

        if ($value) {
            Assert::same(
                $headerValue,
                $value,
                \sprintf("Header [%s] was found, but value [%s] does not match [%s].", $name, $headerValue, $value),
            );
        }

        return $this;
    }

    #[AssertMethod]
    public function assertHeaderMissing(string $name): self
    {
        Assert::false(
            $this->response->hasHeader($name),
            \sprintf('Response contains header with name [%s].', $name),
        );

        return $this;
    }

    #[AssertMethod]
    public function assertStatus(int $status): self
    {
        Assert::same(
            $this->response->getStatusCode(),
            $status,
            \sprintf(
                "Received response status code [%s : %s] but expected %s. Body: %s",
                $this->response->getStatusCode(),
                $this->response->getReasonPhrase(),
                $status,
                (string) $this->response->getBody(),
            ),
        );

        return $this;
    }

    #[AssertMethod]
    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }

    #[AssertMethod]
    public function assertCreated(): self
    {
        return $this->assertStatus(201);
    }

    #[AssertMethod]
    public function assertAccepted(): self
    {
        return $this->assertStatus(202);
    }

    #[AssertMethod]
    public function assertNoContent(int $status = 204): self
    {
        $this->assertStatus($status);

        Assert::blank(
            $this->response->getBody()->getContents(),
            'Response content should be empty.',
        );

        return $this;
    }

    #[AssertMethod]
    public function assertNotFound(): self
    {
        return $this->assertStatus(404);
    }

    #[AssertMethod]
    public function assertForbidden(): self
    {
        return $this->assertStatus(403);
    }

    #[AssertMethod]
    public function assertUnauthorized(): self
    {
        return $this->assertStatus(401);
    }

    #[AssertMethod]
    public function assertUnprocessable(): self
    {
        return $this->assertStatus(422);
    }

    #[AssertMethod]
    public function assertBodySame(string $needle): self
    {
        Assert::same(
            (string) $this->response->getBody(),
            $needle,
            \sprintf('Response is not same with [%s]', $needle),
        );

        return $this;
    }

    #[AssertMethod]
    public function assertBodyNotSame(string $needle): self
    {
        Assert::notSame(
            (string) $this->response->getBody(),
            $needle,
            \sprintf('Response is same with [%s]', $needle),
        );

        return $this;
    }

    /**
     * @param non-empty-string $needle
     */
    #[AssertMethod]
    public function assertBodyContains(string $needle): self
    {
        Assert::string((string) $this->response->getBody())->contains(
            $needle,
            \sprintf('Response doesn\'t contain [%s]', $needle),
        );

        return $this;
    }

    #[AssertMethod]
    public function assertCookieExists(string $key): self
    {
        Assert::true(
            \array_key_exists($key, $this->getCookies()),
            \sprintf('Response doesn\'t have cookie with name [%s]', $key),
        );

        return $this;
    }

    #[AssertMethod]
    public function assertCookieMissed(string $key): self
    {
        Assert::false(
            \array_key_exists($key, $this->getCookies()),
            \sprintf('Response has cookie with name [%s]', $key),
        );

        return $this;
    }

    #[AssertMethod]
    public function assertCookieSame(string $key, mixed $value): self
    {
        $this->assertCookieExists($key);

        Assert::same(
            $this->cookies[$key],
            $value,
            \sprintf('Response cookie with name [%s] is not equal.', $key),
        );

        return $this;
    }

    public function isRedirect(): bool
    {
        return \in_array($this->response->getStatusCode(), [201, 301, 302, 303, 307, 308]);
    }

    public function getOriginalResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return array<non-empty-string, string>
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getJsonParsedBody(): array
    {
        return \json_decode(
            (string) $this->response->getBody(),
            true,
        );
    }

    public function __toString(): string
    {
        return (string) $this->getOriginalResponse()->getBody();
    }

    private function fetchCookies(array $header): array
    {
        $result = [];
        foreach ($header as $line) {
            $cookie = explode('=', $line);
            $result[$cookie[0]] = rawurldecode(substr($cookie[1], 0, strpos($cookie[1], ';')));
        }

        return $result;
    }
}
