<?php

declare(strict_types=1);

namespace Spiral\Testing\Mailer;

use Closure;
use PHPUnit\Framework\TestCase;
use Spiral\Mailer\MailerInterface;
use Spiral\Mailer\MessageInterface;

class FakeMailer implements MailerInterface
{
    private array $messages = [];

    private function filterMessages(string $type, Closure $callback = null): array
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

    /**
     * @return MessageInterface[]
     */
    public function assertSent(string $message, Closure $callback = null): array
    {
        $messages = $this->filterMessages($message, $callback);

        TestCase::assertTrue(
            \count($messages) > 0,
            \sprintf('The expected [%s] message was not sent.', $message)
        );

        return $messages;
    }

    public function assertNotSent(string $message, Closure $callback = null): void
    {
        $messages = $this->filterMessages($message, $callback);

        TestCase::assertCount(
            0,
            $messages,
            \sprintf('The unexpected [%s] message was sent.', $message)
        );
    }

    /**
     * @return MessageInterface[]
     */
    public function assertSentTimes(string $message, int $times = 1): array
    {
        $messages = $this->filterMessages($message);

        TestCase::assertCount(
            $times,
            $messages,
            \sprintf(
                'The expected [%s] message was sent {%d} times instead of {%d} times.',
                $message,
                \count($messages),
                $times
            )
        );

        return $messages;
    }

    public function assertNothingSent(): void
    {
        $messages = \array_map(static function (MessageInterface $message): string {
            return get_class($message);
        }, $this->messages);

        $messages = \implode(', ', $messages);

        TestCase::assertCount(
            0,
            $this->messages,
            \sprintf(
                'The following messages were sent unexpectedly: %s.',
                $messages
            )
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
}
