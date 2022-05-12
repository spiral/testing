<?php

declare(strict_types=1);

namespace Spiral\Testing\Storage;

use PHPUnit\Framework\TestCase;
use Spiral\Storage\Bucket;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\FileInterface;

class FakeBucket extends Bucket
{
    private array $deleted = [];
    private array $created = [];
    private array $copied = [];
    private array $moved = [];
    private array $visibility = [];

    private function filterFiles(array $files, \Closure $callback): array
    {
        return \array_filter($files, static function (array $data) use ($callback) {
            return $callback($data);
        });
    }

    public function assertExists(string $pathname): void
    {
        TestCase::assertTrue(
            $this->file($pathname)->exists(),
            \sprintf('The expected [%s] files is not exist.', $pathname)
        );
    }

    public function assertNotExist(string $pathname): void
    {
        TestCase::assertFalse(
            $this->file($pathname)->exists(),
            \sprintf('The unexpected [%s] files is exist.', $pathname)
        );
    }

    public function assertCreated(string $pathname): void
    {
        $files = $this->filterFiles($this->created, function (array $data) use($pathname) {
            return $data['pathname'] === $pathname;
        });

        TestCase::assertTrue(
            \count($files) > 0,
            \sprintf('The expected [%s] files was not created.', $pathname)
        );
    }

    public function assertNotCreated(string $pathname): void
    {
        $files = $this->filterFiles($this->created, function (array $data) use($pathname) {
            return $data['pathname'] === $pathname;
        });

        TestCase::assertTrue(
            \count($files) === 0,
            \sprintf('The expected [%s] files was created.', $pathname)
        );
    }

    public function create(string $pathname, array $config = []): FileInterface
    {
        $file = parent::create($pathname, $config);

        $this->created[] = \compact('pathname', 'config', 'file');

        return $file;
    }

    public function write(string $pathname, $content, array $config = []): FileInterface
    {
        $file = parent::write($pathname, $content, $config);

        $this->created[] = \compact('pathname', 'config', 'file');

        return $file;
    }

    public function assertVisibilityChanged(string $pathname): void
    {
        $files = $this->filterFiles($this->visibility, function (array $data) use($pathname) {
            return $data['pathname'] === $pathname;
        });

        TestCase::assertTrue(
            \count($files) > 0,
            \sprintf('The expected [%s] files visibility was not changed.', $pathname)
        );
    }

    public function assertVisibilityNotChanged(string $pathname): void
    {
        $files = $this->filterFiles($this->visibility, function (array $data) use($pathname) {
            return $data['pathname'] === $pathname;
        });

        TestCase::assertTrue(
            \count($files) === 0,
            \sprintf('The expected [%s] files visibility was changed.', $pathname)
        );
    }

    public function setVisibility(string $pathname, string $visibility): FileInterface
    {
        $file = parent::setVisibility($pathname, $visibility);

        $this->visibility[] = \compact('pathname', 'visibility', 'file');

        return $file;
    }

    public function assertCopied(string $pathname, string $destination): void
    {
        $files = $this->filterFiles($this->copied, function (array $data) use($pathname, $destination) {
            return $data['pathname'] === $pathname && $data['destination'] === $destination;
        });

        TestCase::assertTrue(
            \count($files) > 0,
            \sprintf('The expected [%s] files was not copied.', $pathname)
        );
    }

    public function assertNotCopied(string $pathname, string $destination): void
    {
        $files = $this->filterFiles($this->copied, function (array $data) use($pathname, $destination) {
            return $data['pathname'] === $pathname && $data['destination'] === $destination;
        });

        TestCase::assertTrue(
            \count($files) === 0,
            \sprintf('The expected [%s] files was copied.', $pathname)
        );
    }

    public function copy(
        string $pathname,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface {
        $file = parent::copy($pathname, $destination, $storage, $config);

        $this->copied[] = \compact('pathname', 'destination', 'storage', 'file');

        return $file;
    }

    public function assertMoved(string $pathname, string $destination): void
    {
        $files = $this->filterFiles($this->moved, function (array $data) use($pathname, $destination) {
            return $data['pathname'] === $pathname && $data['destination'] === $destination;
        });

        TestCase::assertTrue(
            \count($files) > 0,
            \sprintf('The expected [%s] files was not moved.', $pathname)
        );
    }

    public function assertNotMoved(string $pathname, string $destination): void
    {
        $files = $this->filterFiles($this->moved, function (array $data) use($pathname, $destination) {
            return $data['pathname'] === $pathname && $data['destination'] === $destination;
        });

        TestCase::assertTrue(
            \count($files) === 0,
            \sprintf('The expected [%s] files was moved.', $pathname)
        );
    }

    public function move(
        string $pathname,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface {
        $file = parent::move($pathname, $destination, $storage, $config);

        $this->moved[] = \compact('pathname', 'destination', 'storage', 'file');

        return $file;
    }

    public function assertDeleted(string $pathname): void
    {
        $files = $this->filterFiles($this->deleted, function (array $data) use($pathname) {
            return $data['pathname'] === $pathname;
        });

        TestCase::assertTrue(
            \count($files) > 0,
            \sprintf('The expected [%s] files was not deleted.', $pathname)
        );
    }

    public function assertNotDeleted(string $pathname): void
    {
        $files = $this->filterFiles($this->deleted, function (array $data) use($pathname) {
            return $data['pathname'] === $pathname;
        });

        TestCase::assertTrue(
            \count($files) === 0,
            \sprintf('The expected [%s] files was deleted.', $pathname)
        );
    }

    public function delete(string $pathname, bool $clean = false): void
    {
        parent::delete($pathname, $clean);

        $this->deleted[] = \compact('pathname', 'clean');
    }
}
