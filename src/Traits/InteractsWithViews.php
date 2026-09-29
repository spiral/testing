<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Views\ViewsInterface;
use Testo\Assert;
use Testo\Common\Attribute\AssertMethod;

trait InteractsWithViews
{
    final public function getViews(): ViewsInterface
    {
        return $this->getContainer()->get(ViewsInterface::class);
    }

    #[AssertMethod]
    public function assertViewSame(string $path, array $data = [], string $expected = ''): void
    {
        Assert::same(
            $this->getViews()->render($path, $data),
            $expected,
        );
    }

    #[AssertMethod]
    public function assertViewContains(string $path, array $data = [], array|string $strings = ''): void
    {
        $result = $this->getViews()->render($path, $data);

        foreach ((array) $strings as $string) {
            Assert::string($result)->contains($string);
        }
    }

    #[AssertMethod]
    public function assertViewNotContains(string $path, array $data = [], array|string $strings = ''): void
    {
        $result = $this->getViews()->render($path, $data);

        foreach ((array) $strings as $string) {
            Assert::string($result)->notContains($string);
        }
    }
}
