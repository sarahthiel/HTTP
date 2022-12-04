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

namespace sebastianthiel\HTTP\ServerRequest;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use sebastianthiel\HTTP\Request\Request;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected $serverParams  = [];
    protected $cookieParams  = [];
    protected $queryParams   = [];
    protected $uploadedFiles = [];
    protected $attributes    = [];
    protected $parsedBody;

    /**
     * ServerRequest constructor.
     *
     * @param string          $method
     * @param UriInterface    $uri
     * @param StreamInterface $body
     * @param array           $headers
     * @param array           $serverParams
     * @param array|null      $queryParams
     * @param array           $cookieParams
     * @param array           $uploadedFiles
     * @param array           $attributes
     * @param string          $version
     */
    public function __construct(
        $method,
        UriInterface $uri,
        StreamInterface $body,
        array $headers = [],
        array $serverParams = [],
        array $queryParams = null,
        array $cookieParams = [],
        array $uploadedFiles = [],
        array $attributes = [],
        $version = '1.1'
    ) {
        parent::__construct($method, $uri, $body, $headers, $version);
        $this->serverParams = $serverParams;

        if (!$queryParams){
            parse_str($this->getUri()->getQuery(), $this->queryParams);
        } else {
            $this->queryParams = $queryParams;
        }
        $this->cookieParams = $cookieParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->attributes = $attributes;
    }

    /**
     * Retrieve server parameters.
     *
     * @return array
     */
    public function getServerParams() : array
    {
        return $this->serverParams;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams() : array
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     *
     * @return ServerRequestInterface
     */
    public function withCookieParams(array $cookies) : ServerRequestInterface
    {
        $request = clone $this;
        $request->cookieParams = $cookies;

        return $request;
    }

    /**
     * Retrieve query string arguments.
     *
     * @return array
     */
    public function getQueryParams() : array
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     *
     * @return ServerRequestInterface
     */
    public function withQueryParams(array $query) : ServerRequestInterface
    {
        $request = clone $this;
        $request->queryParams = $query;

        return $request;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles() : array
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     *
     * @return ServerRequestInterface
     */
    public function withUploadedFiles(array $uploadedFiles) : ServerRequestInterface
    {
        $request = clone $this;
        $request->uploadedFiles = $uploadedFiles;

        return $request;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     *
     * @return ServerRequestInterface
     */
    public function withParsedBody($data) : ServerRequestInterface
    {
        $request = clone $this;
        $request->parsedBody = $data;

        return $request;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return (isset($this->attributes[$name]))
            ? $this->attributes[$name]
            : $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     *
     * @return ServerRequestInterface
     */
    public function withAttribute($name, $value) : ServerRequestInterface
    {
        $request = clone $this;
        $request->attributes[$name] = $value;

        return $request;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param string $name The attribute name.
     *
     * @return ServerRequestInterface
     */
    public function withoutAttribute($name) : ServerRequestInterface
    {
        $request = clone $this;
        unset($request->attributes[$name]);

        return $request;
    }
}
