<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Views\ViewsInterface;

trait InteractsWithViews
{
    final public function getViews(): ViewsInterface
    {
        return $this->getContainer()->get(ViewsInterface::class);
    }

    public function assertViewSame(string $path, array $data = [], string $expected = ''): void
    {
        $this->assertSame(
            $expected,
            $this->getViews()->render($path, $data)
        );
    }

    public function assertViewContains(string $path, array $data = [], array|string $strings = ''): void
    {
        $result = $this->getViews()->render($path, $data);

        foreach ((array)$strings as $string) {
            $this->assertStringContainsString($string, $result);
        }
    }

    public function assertViewNotContains(string $path, array $data = [], array|string $strings = ''): void
    {
        $result = $this->getViews()->render($path, $data);

        foreach ((array)$strings as $string) {
            $this->assertStringNotContainsString($string, $result);
        }
    }
}
