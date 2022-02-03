<?php

declare(strict_types=1);

namespace Spiral\Testing\Queue;

use PHPUnit\Framework\TestCase;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Options;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueTrait;

class FakeQueue implements QueueInterface
{
    use QueueTrait;

    /**
     * @var array<int, array{name: string, payload: array<string, mixed>, options: OptionsInterface}>
     */
    private array $jobs = [];

    public function __construct(
        private HandlerRegistryInterface $registry,
        private string $name,
        private string $driver
    ) {
    }

    private function filterJobs(string $name, \Closure $callback = null): array
    {
        $jobs = $this->jobs[$name] ?? [];

        $callback = $callback ?: static function (): bool {
            return true;
        };

        return \array_filter($jobs, static function (array $data) use ($callback) {
            return $callback($data);
        });
    }

    public function assertPushed(string $name, \Closure $callback = null): void
    {
        $jobs = $this->filterJobs($name, $callback);

        TestCase::assertTrue(
            \count($jobs) > 0,
            \sprintf('The expected job [%s] was not pushed.', $name)
        );
    }

    public function assertNotPushed(string $name, \Closure $callback = null): void
    {
        $jobs = $this->filterJobs($name, $callback);

        TestCase::assertCount(
            0,
            $jobs,
            \sprintf('The unexpected job [%s] was pushed.', $name)
        );
    }

    public function assertNothingPushed(): void
    {
        $jobs = \implode(', ', \array_keys($this->jobs));

        TestCase::assertCount(
            0,
            $this->jobs,
            \sprintf('The following jobs were pushed unexpectedly: %s', $jobs)
        );
    }

    public function assertPushedTimes(string $name, int $times = 1): void
    {
        $jobs = $this->filterJobs($name);

        TestCase::assertCount(
            $times,
            $jobs,
            \sprintf(
                'The expected job [%s] was sent {%d} times instead of {%d} times.',
                $name,
                \count($jobs),
                $times
            )
        );
    }

    public function assertPushedOnQueue(string $queue, string $name, \Closure $callback = null): void
    {
        $this->assertPushed($name, static function (array $data) use ($queue, $callback) {
            if ($data['options']->getQueue() !== $queue) {
                return false;
            }

            return $callback ? $callback($data) : true;
        });
    }

    public function push(string $name, array $payload = [], OptionsInterface $options = null): string
    {
        $this->jobs[$name][] = [
            'name' => $name,
            'handler' => $this->registry->getHandler($name),
            'payload' => $payload,
            'options' => $options ?? new Options(),
        ];

        return 'job-id';
    }
}
