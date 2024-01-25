<?php

declare(strict_types=1);

namespace Spiral\Testing\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_METHOD)]
final class TestScope
{
    public function __construct(
        public readonly string|\BackedEnum $scope,
        public readonly array $bindings = [],
    ) {
    }
}
