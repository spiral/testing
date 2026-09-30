<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Testing\Internal\RethrowingExceptionHandler;

trait InteractsWithExceptions
{
    protected function withoutExceptionHandling(): void
    {
        RethrowingExceptionHandler::install($this->getContainer());
    }
}
