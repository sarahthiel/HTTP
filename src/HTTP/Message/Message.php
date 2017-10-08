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

namespace sebastianthiel\HTTP\Message;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    /** @var array */
    const SPECIAL_HEADER = [
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'CONTENT_MD5',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE',
        'COOKIE'
    ];


    /** @var string */
    protected $protocolVersion = '1.1';

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $headerKeys = [];

    /** @var \Psr\Http\Message\StreamInterface */
    protected $body;

    /** @var StreamFactoryInterface */
    protected $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion() : string
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param string $version HTTP protocol version
     *
     * @return MessageInterface
     */
    public function withProtocolVersion($version) : MessageInterface
    {
        $message = clone $this;
        $message->protocolVersion = $version;

        return $message;
    }

    /**
     * Retrieves all message header values.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name) : bool
    {
        return isset($this->headerKeys[strtolower($name)]);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name) : array
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$this->headerKeys[strtolower($name)]];
        }

        return [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name) : string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value) : MessageInterface
    {
        if (!is_array($value)) {
            $value = [(string) $value];
        }

        $message = clone $this;
        $message->headerKeys[strtolower($name)] = $name;
        $message->headers[$name] = $value;

        return $message;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value) : MessageInterface
    {
        if (!is_array($value)) {
            $value = [(string) $value];
        }

        $headerKey = $this->hasHeader($name) ? $this->headerKeys[strtolower($name)] : $name;

        $message = clone $this;
        $message->headers[$headerKey] = array_merge($this->getHeader($name), $value);
        $message->headerKeys[strtolower($name)] = $name;

        return $message;
    }

    /**
     * Return an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return MessageInterface
     */
    public function withoutHeader($name) : MessageInterface
    {
        $headerKey = $this->hasHeader($name) ? $this->headerKeys[strtolower($name)] : $name;

        $message = clone $this;
        unset($message->headerKeys[strtolower($name)], $message->headers[$headerKey]);

        return $message;
    }

    /**
     * add an array of headers to the Message
     *
     * @param $headers
     */
    protected function addHeaders($headers) : void
    {

        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            $headerKey = $this->hasHeader($header) ? $this->headerKeys[strtolower($header)] : $header;
            $this->headers[$headerKey] = array_merge($this->getHeader($headerKey), $value);
            $this->headerKeys[strtolower($headerKey)] = $headerKey;
        }
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody() : StreamInterface
    {
        if (!$this->body) {
            $this->body = $this->streamFactory->createStream();
        }

        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * @param StreamInterface $body Body.
     *
     * @return MessageInterface
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body) : MessageInterface
    {
        $message = clone $this;
        $message->body = $body;

        return $message;
    }
}
