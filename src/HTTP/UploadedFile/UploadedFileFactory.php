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

use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /** @var  StreamFactoryInterface */
    protected $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * Create a new uploaded file.
     *
     * @param string|resource $file
     * @param int             $size  in bytes
     * @param int             $error PHP file upload error
     * @param string          $clientFilename
     * @param string          $clientMediaType
     *
     * @return UploadedFileInterface
     *
     * @throws \InvalidArgumentException
     *  If the file resource is not readable.
     */
    public function createUploadedFile(
        $file,
        $size = null,
        $error = \UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    ) : UploadedFileInterface {
        switch (true) {
            case is_string($file):
                $stream = $this->streamFactory->createStream($file);
                break;
            case is_resource($file):
                $stream = $this->streamFactory->createStreamFromResource($file);
                break;
            default:
                $stream = null;
        }

        if (!$stream || !$stream->isReadable()) {
            throw new \InvalidArgumentException('Can not read from file resource');
        }

        if (!$size) {
            $size = $stream->getSize();
        }

        return new UploadedFile($stream, $error, $size, $clientFilename, $clientMediaType);
    }
}
