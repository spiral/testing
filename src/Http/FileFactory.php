<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

final class FileFactory
{
    /**
     * Create a new fake file.
     */
    public function createFile(string $filename, ?int $kilobytes = null, string $mimeType = null): File
    {
        $file = new File($filename, tmpfile());

        if ($kilobytes !== null) {
            $file->setSize($kilobytes);
        }

        if ($mimeType) {
            $file->setMimeType($mimeType);
        }

        return $file;
    }

    /**
     * Create a new fake file with given content.
     */
    public function createFileWithContent(string $filename, string $content, string $mimeType = null): File
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $content);

        $file = new File($filename, $tmpFile);

        if ($mimeType) {
            $file->setMimeType($mimeType);
        }

        return $file;
    }

    public function createImage(string $filename, int $width = 50, int $height = 50): File
    {
        $tmpFile = tmpfile();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        ob_start();

        $extension = in_array($extension, ['jpeg', 'png', 'gif', 'webp', 'wbmp', 'bmp'])
            ? strtolower($extension)
            : 'jpeg';

        $image = \imagecreatetruecolor($width, $height);

        call_user_func("image{$extension}", $image);

        fwrite($tmpFile, ob_get_clean());

        return new File($filename, $tmpFile);
    }
}
