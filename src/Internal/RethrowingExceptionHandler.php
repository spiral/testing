<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Spiral\Core\Container;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;

/**
 * Lets exceptions escape to the test instead of being rendered or reported.
 *
 * @internal
 */
final class RethrowingExceptionHandler implements ExceptionHandlerInterface, ExceptionRendererInterface
{
    public static function install(Container $container): void
    {
        $container->removeBinding(ExceptionHandlerInterface::class);
        $container->bind(ExceptionHandlerInterface::class, new self());
    }

    public function register(): void {}

    public function handleGlobalException(\Throwable $e): void {}

    public function getRenderer(?string $format = null): ?ExceptionRendererInterface
    {
        return $this;
    }

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = Verbosity::BASIC,
        ?string $format = null,
    ): string {
        throw $exception;
    }

    public function canRender(string $format): bool
    {
        return true;
    }

    public function report(\Throwable $exception): void
    {
        throw $exception;
    }
}
