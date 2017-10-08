<?php
/**
 * sebastianthiel HTTP
 *
 * @package    sebastianthiel/HTTP
 * @author     Sebastian Thiel <me@sebastian-thiel.eu>
 * @license    https://opensource.org/licenses/MIT  MIT
 * @version    0.1
 */

declare(strict_types=1);

namespace sebastianthiel\HTTP\UploadedFile;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use sebastianthiel\HTTP\Exception\UploadedFileException;

class UploadedFile implements UploadedFileInterface
{
    /** @var StreamInterface */
    protected $stream;

    /** @var int */
    protected $error;

    /** @var bool */
    protected $moved = false;

    /** @var int */
    protected $size;

    /** @var string */
    protected $clientFilename;

    /** @var string */
    protected $clientMediaType;

    /**
     * UploadedFile constructor.
     *
     * @param StreamInterface $stream
     * @param int             $error
     * @param null|int        $size
     * @param null|string     $clientFilename
     * @param null|string     $clientMediaType
     */
    public function __construct(
        StreamInterface $stream,
        int $error = UPLOAD_ERR_OK,
        $size = null,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        $this->stream = $stream;
        $this->error = (int) $error;
        $this->size = (is_null($size)) ? null : (int) $size;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws UploadedFileException
     */
    public function getStream() : StreamInterface
    {
        if ($this->moved) {
            throw new UploadedFileException('This file was already moved.');
        }

        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $targetPath Path to which to move the uploaded file.
     *
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws UploadedFileException
     */
    public function moveTo($targetPath) : void
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new UploadedFileException('Cannot retrieve stream due to upload error');
        }

        $source = $this->getStream();
        $uri = $source->getMetadata('uri');
        if ($source->getMetadata('stream_type') === 'STDIO' && !is_null($uri)) {
            $this->moveUploadedFile($uri, $targetPath);

            return;
        }

        $this->writeStreamToFile($targetPath);
    }

    /**
     * move a file
     *
     * @param $sourcePath
     * @param $targetPath
     *
     * @throws UploadedFileException
     */
    protected function moveUploadedFile($sourcePath, $targetPath) : void
    {
        if (!move_uploaded_file($sourcePath, $targetPath)) {
            throw new UploadedFileException('Failed to move uploaded file.');
        }
        $this->moved = true;
    }

    /**
     * write a stream to a file
     *
     * @param $targetPath
     *
     * @throws UploadedFileException
     */
    protected function writeStreamToFile($targetPath) : void
    {
        $targetResource = fopen($targetPath, 'wb');
        if (!$targetResource) {
            throw new UploadedFileException('Unable to write stream to destination.');
        }

        $source = $this->getStream();
        $source->rewind();
        while (!$source->eof()) {
            fwrite($targetResource, $source->read(4096));
        }
        fclose($targetResource);
    }

    /**
     * Retrieve the file size.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError() : int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}
