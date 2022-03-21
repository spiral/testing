<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Storage;

use Spiral\Testing\Tests\TestCase;

final class StorageBucketFakerTest extends TestCase
{
    private \Spiral\Storage\StorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->fakeStorage();
    }

    public function testWrite()
    {
        $image = $this->getFileFactory()->createImage('image.jpg');
        $file = $this->getFileFactory()->createFile('file.txt');

        $uploads = $this->storage->bucket('uploads');
        $public = $this->storage->bucket('public');

        $uploads->write($image->getClientFilename(), $image->getStream());
        $public->write($file->getClientFilename(), $file->getStream());

        $uploads->assertExists('image.jpg');
        $uploads->assertNotExist('file.txt');
        $uploads->assertCreated('image.jpg');

        $public->assertExists('file.txt');
        $public->assertNotExist('image.jpg');
    }
}
