<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

final class FileFactory
{
    /**
     * Create a new fake file.
     */
    public function createFile(string $filename, ?int $kilobytes = null, ?string $mimeType = null): File
    {
        $file = new File($filename, $this->createTempFile());

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
    public function createFileWithContent(string $filename, string $content, ?string $mimeType = null): File
    {
        $tmpFile = $this->createTempFile();
        fwrite($tmpFile, $content);

        $file = new File($filename, $tmpFile);

        if ($mimeType) {
            $file->setMimeType($mimeType);
        }

        return $file;
    }

    public function createImage(string $filename, int $width = 50, int $height = 50): File
    {
        $tmpFile = $this->createTempFile();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        ob_start();

        $extension = in_array($extension, ['jpeg', 'png', 'gif', 'webp', 'wbmp', 'bmp'])
            ? strtolower($extension)
            : 'jpeg';

        $image = \imagecreatetruecolor($width, $height);

        call_user_func("image{$extension}", $image);

        fwrite($tmpFile, (string) ob_get_clean());

        return new File($filename, $tmpFile);
    }

    /**
     * @return resource
     */
    private function createTempFile()
    {
        $tmpFile = tmpfile();

        if ($tmpFile === false) {
            throw new \RuntimeException('Unable to create a temporary file.');
        }

        return $tmpFile;
    }
}
