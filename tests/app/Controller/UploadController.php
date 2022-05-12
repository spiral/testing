<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\App\Controller;

use Spiral\Http\Request\InputManager;
use Spiral\Router\Annotation\Route;
use Spiral\Storage\StorageInterface;

final class UploadController
{
    #[Route('/upload', 'upload')]
    public function upload(StorageInterface $storage, InputManager $input): string
    {
        $image = $input->file('image');

        $storage->bucket('uploads')->write(
            $image->getClientFilename(),
            $image->getStream()
        );

        return $image->getClientFilename();
    }
}
