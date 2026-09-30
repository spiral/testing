<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Core\FactoryInterface;
use Spiral\Testing\Http\FakeHttp;
use Spiral\Testing\Http\FileFactory;
use Spiral\Testing\Internal\ScopeRunner;

trait InteractsWithHttp
{
    final public function getFileFactory(): FileFactory
    {
        return new FileFactory();
    }

    final public function fakeHttp(): FakeHttp
    {
        return $this->getContainer()->get(FactoryInterface::class)->make(FakeHttp::class, [
            'fileFactory' => $this->getFileFactory(),
            'scope' => /** @param array<non-empty-string, callable|non-empty-string|object|list{class-string, non-empty-string}> $bindings */ function (\Closure $closure, array $bindings = []) {
                return ScopeRunner::run(['http'], $closure, $this->getContainer(), $bindings);
            },
        ]);
    }
}
