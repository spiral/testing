<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Nyholm\Psr7\Uri;
use Spiral\Distribution\Resolver\StaticResolver;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\StorageInterface;
use Spiral\Testing\Storage\FakeBucketFactory;

trait InteractsWithStorage
{
    public function fakeStorage(): StorageInterface
    {
        $factory = new FakeBucketFactory(
            $root = $this->getDirectoryByAlias('runtime', 'testing/disks')
        );

        $this->cleanupDirectories($root);

        $storage = $this->getContainer()->get(StorageInterface::class);
        $config = $this->getContainer()->get(StorageConfig::class);

        foreach ($config->getAdapters() as $name => $adapter) {
            $storage->add(
                $name,
                $factory->createFromAdapter($adapter, $name, new StaticResolver(new Uri('http://127.0.0.1/public'))),
                true
            );
        }

        return $storage;
    }
}
