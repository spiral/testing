<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Testo\Core\Value\CaseInstance;

/**
 * @internal
 */
final readonly class TestCaseInstance implements CaseInstance
{
    public function __construct(
        private object $instance,
    ) {}

    #[\Override]
    public function getInstance(): object
    {
        return $this->instance;
    }

    #[\Override]
    public function hasInstance(): bool
    {
        return true;
    }
}
