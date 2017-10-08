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

namespace sebastianthiel\HTTP\Stream;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{

    /**
     * Create a new stream from a string.
     *
     * @param string $content
     *
     * @return StreamInterface
     */
    public function createStream($content = '') : StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }

    /**
     * Create a stream from an existing file.
     *
     * @param string $filename
     * @param string $mode
     *
     * @return StreamInterface
     */
    public function createStreamFromFile($filename, $mode = 'r') : StreamInterface
    {
        $resource = fopen($filename, $mode);
        $stream = new Stream($resource);

        return $stream;
    }

    /**
     * Create a new stream from an existing resource.
     *
     * @param resource $resource
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($resource) : StreamInterface
    {
        return new Stream($resource);
    }
}
