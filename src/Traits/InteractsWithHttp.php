<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Testing\Http\FakeHttp;
use Spiral\Testing\Http\FileFactory;

trait InteractsWithHttp
{
    protected function fakeHttp(): FakeHttp
    {
        return new FakeHttp(
            $this->getContainer(),
            new FileFactory(),
            function (\Closure $closure, array $bindings = []) {
                return $this->runScoped($closure, $bindings);
            }
        );
    }
}
