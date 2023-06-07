<?php

declare(strict_types=1);

namespace Spiral\Testing\Queue;

use PHPUnit\Framework\TestCase;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Options;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueTrait;

/**
 * @psalm-type TJob = array{
 *     name: string,
 *     payload: array,
 *     options: Options,
 *     handler: ?\Spiral\Queue\HandlerInterface
 * }
 */
class FakeQueue implements QueueInterface
{
    use QueueTrait;

    /**
     * @var array<non-empty-string, array<int, array{
     *     name: string,
     *     payload: array<string, mixed>,
     *     options: OptionsInterface
     * }>>
     */
    private array $jobs = [];

    public function __construct(
        private readonly HandlerRegistryInterface $registry,
        private readonly string                   $name,
        private readonly string                   $driver
    )
    {
    }

    /**
     * @return list<TJob>
     */
    private function filterJobs(string $name, \Closure $callback = null): array
    {
        $jobs = $this->jobs[$name] ?? [];

        $callback = $callback ?: static function (array $data): bool {
            return true;
        };

        return \array_filter($jobs, static function (array $data) use ($callback) {
            return $callback($data);
        });
    }

    /**
     * @return list<TJob>
     */
    public function assertPushed(string $name, \Closure $callback = null): array
    {
        $jobs = $this->filterJobs($name, $callback);

        TestCase::assertTrue(
            \count($jobs) > 0,
            \sprintf('The expected job [%s] was not pushed.', $name)
        );

        return $jobs;
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

    /**
     * @return list<TJob>
     */
    public function assertPushedTimes(string $name, int $times = 1): array
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

        return $jobs;
    }

    /**
     * @return list<TJob>
     */
    public function assertPushedOnQueue(string $queue, string $name, \Closure $callback = null): array
    {
        return $this->assertPushed($name, static function (array $data) use ($queue, $callback) {
            if ($data['options']->getQueue() !== $queue) {
                return false;
            }

            return $callback ? $callback($data) : true;
        });
    }

    public function push(string $name, mixed $payload = [], OptionsInterface $options = null): string
    {
        $this->jobs[$name][] = [
            'name' => $name,
            'handler' => \class_exists($name) ? $this->registry->getHandler($name): null,
            'payload' => $payload,
            'options' => $options ?? new Options(),
        ];

        return 'job-id';
    }

    public function clear(): void
    {
        $this->jobs = [];
    }
}
