<?php

declare(strict_types=1);

namespace Spiral\Testing\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\BucketFactoryInterface;
use Spiral\Storage\BucketInterface;

final class FakeBucketFactory implements BucketFactoryInterface
{
    public function __construct(
        private readonly string $path
    ) {
    }

    public function createFromAdapter(
        FilesystemAdapter $adapter,
        string $name = null,
        UriResolverInterface $resolver = null
    ): BucketInterface {
        return new FakeBucket(
            new Filesystem(
                new LocalFilesystemAdapter($this->path . '/' . $name)
            ),
            $name,
            $resolver
        );
    }
}
