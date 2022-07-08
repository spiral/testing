<?php

declare(strict_types=1);

namespace Spiral\Testing\Http;

use Nyholm\Psr7\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class File extends UploadedFile
{
    /**
     * The fake file size
     */
    public ?int $fakeSize = null;

    /**
     * The fake file size
     */
    public ?string $fakeMimeType = null;

    /**
     * @param string $filename
     * @param resource $tempFile
     */
    public function __construct(
        private string $filename,
        private $tempFile
    ) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeType = (new MimeTypes())->getMimeTypes($extension)[0] ?? 'application/octet-stream';

        parent::__construct(
            $this->tempFilePath(),
            fstat($tempFile)['size'],
            UPLOAD_ERR_OK,
            $filename,
            $mimeType
        );
    }

    /**
     * Set the fake size of the file in kilobytes.
     */
    public function setSize(int $kilobytes): void
    {
        $this->fakeSize = $kilobytes * 1024;
    }

    /**
     * Set the fake MIME type for the file.
     */
    public function setMimeType(string $mimeType): void
    {
        $this->fakeMimeType = $mimeType;
    }

    public function getClientMediaType(): ?string
    {
        if ($this->fakeMimeType !== null) {
            return $this->fakeMimeType;
        }

        return parent::getClientMediaType();
    }

    public function getSize(): int
    {
        if ($this->fakeSize !== null) {
            return $this->fakeSize;
        }

        return parent::getSize();
    }

    /**
     * Get the path to the temporary file.
     */
    protected function tempFilePath(): string
    {
        return stream_get_meta_data($this->tempFile)['uri'];
    }
}
