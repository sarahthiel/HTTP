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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use sebastianthiel\HTTP\Exception\InvalidMethodException;
use sebastianthiel\HTTP\Message\Message;

class Request extends Message implements RequestInterface
{
    const HTTP_METHODS = [
        'CONNECT' => true,
        'DELETE'  => true,
        'GET'     => true,
        'HEAD'    => true,
        'OPTIONS' => true,
        'PATCH'   => true,
        'POST'    => true,
        'PUT'     => true,
        'TRACE'   => true
    ];
    /** @var string */
    private $method;

    /** @var null|string */
    private $requestTarget;

    /** @var UriInterface */
    private $uri;
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Request constructor.
     *
     * @param string          $method
     * @param UriInterface    $uri
     * @param StreamInterface $body
     * @param array           $headers
     * @param string          $version
     */
    public function __construct(
        string $method,
        UriInterface $uri,
        StreamInterface $body,
        array $headers = [],
        $version = '1.1'
    ) {
        $method = strtoupper($method);
        if (!isset(self::HTTP_METHODS[$method])) {
            throw new InvalidMethodException();
        }
        $this->method = $method;
        $this->uri = $uri;
        $this->addHeaders($headers);
        $this->protocolVersion = $version;

        if (!$this->hasHeader('Host')) {
            $this->updateHostByUri();
        }
        $this->body = $body;
    }

    /**
     * Retrieves the message's request target.
     *
     * @return string
     */
    public function getRequestTarget() : string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        $query = $this->uri->getQuery();
        $target .= $query != '' ? '?' . $query : '';

        return $target;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * @param mixed $requestTarget
     *
     * @return RequestInterface
     */
    public function withRequestTarget($requestTarget) : RequestInterface
    {
        $request = clone $this;
        $request->requestTarget = $requestTarget;

        return $request;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * @param string $method Case-sensitive method.
     *
     * @return RequestInterface
     * @throws InvalidMethodException for invalid HTTP methods.
     */
    public function withMethod($method) : RequestInterface
    {
        if (!isset(self::HTTP_METHODS[$method])) {
            throw new InvalidMethodException();
        }
        $request = clone $this;
        $request->method = strtoupper($method);

        return $request;
    }

    /**
     * Retrieves the URI instance.
     *
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri() : UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     *
     * @return RequestInterface
     */
    public function withUri(UriInterface $uri, $preserveHost = false) : RequestInterface
    {
        $request = clone $this;
        $request->uri = $uri;

        if (!$preserveHost) {
            $request->updateHostByUri();
        }

        return $request;
    }

    /**
     * get host from uri and update headers
     */
    protected function updateHostByUri()
    {
        $host = $this->uri->getHost();

        if ($host == '') {
            return;
        }
        $port = $this->uri->getPort();

        if ($port !== null) {
            $host .= ':' . $port;
        }

        if (!isset($this->headerKeys['host'])) {
            $this->headerKeys['host'] = 'Host';
        }

        $header = $this->headerKeys['host'];
        $this->headers = [$header => [$host]] + $this->headers;

        return;
    }
}
