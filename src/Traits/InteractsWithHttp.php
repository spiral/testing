<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Testing\Http\FakeHttp;
use Spiral\Testing\Http\FileFactory;

trait InteractsWithHttp
{
    protected function getFileFactory(): FileFactory
    {
        return new FileFactory();
    }

    protected function fakeHttp(): FakeHttp
    {
        return new FakeHttp(
            $this->getContainer(),
            $this->getFileFactory(),
            function (\Closure $closure, array $bindings = []) {
                return $this->runScoped($closure, $bindings);
            }
        );
    }
}
