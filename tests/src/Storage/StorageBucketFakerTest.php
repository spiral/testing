<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Storage;

use Spiral\Storage\StorageInterface;
use Spiral\Testing\Tests\TestCase;
use Testo\Core\Exception\SkipTest;
use Testo\Test;

final class StorageBucketFakerTest extends TestCase
{
    private StorageInterface $storage;

    #[Test]
    public function testWrite(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new SkipTest('Missed Gd library. Test skipped.');

            return;
        }

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->fakeStorage();
    }
}
