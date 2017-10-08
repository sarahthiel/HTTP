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

namespace sebastianthiel\HTTP\Request;

use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
{
    /** @var UriFactoryInterface */
    protected $uriFactory;

    /** @var StreamFactoryInterface */
    protected $streamFactory;

    public function __construct(UriFactoryInterface $uriFactory, StreamFactoryInterface $streamFactory)
    {
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Create a new request.
     *
     * @param string              $method
     * @param UriInterface|string $uri
     *
     * @return RequestInterface
     */
    public function createRequest($method, $uri) : RequestInterface
    {
        if (!($uri instanceof UriInterface)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        $body = $this->streamFactory->createStream();

        return new Request($method, $uri, $body);
    }
}
