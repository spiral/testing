<?php

declare(strict_types=1);

namespace Spiral\Testing\Storage;

use Spiral\Storage\Bucket;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\FileInterface;

class FakeBucket extends Bucket
{
    private array $deleted = [];
    private array $created = [];
    private array $updated = [];
    private array $copied = [];
    private array $moved = [];
    private array $visibility = [];

    public function create(string $pathname, array $config = []): FileInterface
    {
        $file = parent::create($pathname, $config);

        $this->created[] = \compact('pathname', 'config');

        return $file;
    }

    public function write(string $pathname, $content, array $config = []): FileInterface
    {
        $file = parent::write($pathname, $content, $config);

        $this->updated[] = \compact('pathname', 'config');

        return $file;
    }

    public function setVisibility(string $pathname, string $visibility): FileInterface
    {
        $file = parent::setVisibility($pathname, $visibility);

        $this->visibility[] = \compact('pathname', 'visibility');

        return $file;
    }

    public function copy(
        string $source,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface {
        $file = parent::copy($source, $destination, $storage, $config);

        $this->copied[] = \compact('source', 'destination', 'storage');

        return $file;
    }

    public function move(
        string $source,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface {
        $file = parent::move($source, $destination, $storage, $config);

        $this->moved[] = \compact('source', 'destination', 'storage');

        return $file;
    }

    public function delete(string $pathname, bool $clean = false): void
    {
        parent::delete($pathname, $clean);

        $this->deleted[] = \compact('pathname', 'clean');
    }
}
