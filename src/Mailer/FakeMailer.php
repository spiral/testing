<?php

declare(strict_types=1);

namespace Spiral\Testing\Mailer;

use Spiral\Mailer\MailerInterface;
use Spiral\Mailer\MessageInterface;
use Testo\Assert;
use Testo\Common\Attribute\AssertMethod;

class FakeMailer implements MailerInterface
{
    private array $messages = [];

    /**
     * @return MessageInterface[]
     */
    #[AssertMethod]
    public function assertSent(string $message, ?\Closure $callback = null): array
    {
        $messages = $this->filterMessages($message, $callback);

        Assert::true(
            \count($messages) > 0,
            \sprintf('The expected [%s] message was not sent.', $message),
        );

        return $messages;
    }

    #[AssertMethod]
    public function assertNotSent(string $message, ?\Closure $callback = null): void
    {
        $messages = $this->filterMessages($message, $callback);

        Assert::same(
            \count($messages),
            0,
            \sprintf('The unexpected [%s] message was sent.', $message),
        );
    }

    /**
     * @return MessageInterface[]
     */
    #[AssertMethod]
    public function assertSentTimes(string $message, int $times = 1): array
    {
        $messages = $this->filterMessages($message);

        Assert::same(
            \count($messages),
            $times,
            \sprintf(
                'The expected [%s] message was sent {%d} times instead of {%d} times.',
                $message,
                \count($messages),
                $times,
            ),
        );

        return $messages;
    }

    #[AssertMethod]
    public function assertNothingSent(): void
    {
        $messages = \array_map(static function (MessageInterface $message): string {
            return get_class($message);
        }, $this->messages);

        $messages = \implode(', ', $messages);

        Assert::same(
            \count($this->messages),
            0,
            \sprintf(
                'The following messages were sent unexpectedly: %s.',
                $messages,
            ),
        );
    }

    public function send(MessageInterface ...$message): void
    {
        foreach ($message as $msg) {
            $this->messages[] = $msg;
        }
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    private function filterMessages(string $type, ?\Closure $callback = null): array
    {
        $messages = \array_filter($this->messages, static function (MessageInterface $msg) use ($type): bool {
            return $msg instanceof $type;
        });

        $callback = $callback ?: static function (MessageInterface $msg): bool {
            return true;
        };

        return \array_filter($messages, static function (MessageInterface $msg) use ($callback) {
            return $callback($msg);
        });
    }
}
