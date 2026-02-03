<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Http\Stub;

use Spiral\Boot\FinalizerInterface;

final class FakeFinalizer implements FinalizerInterface
{
    /** @var array<array{terminate: bool}> */
    public array $calls = [];

    public function addFinalizer(callable $finalizer): static
    {
        return $this;
    }

    public function finalize(bool $terminate = false): void
    {
        $this->calls[] = ['terminate' => $terminate];
    }
}
