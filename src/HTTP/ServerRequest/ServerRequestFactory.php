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

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use sebastianthiel\HTTP\Message\Message;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /** @var  UriFactoryInterface */
    protected $uriFactory;

    /** @var  StreamFactoryInterface */
    protected $streamFactory;

    /** @var  UploadedFileFactoryInterface */
    protected $uploadedFileFactory;

    public function __construct(
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

    /**
     * Create a new server request.
     *
     * @param string              $method
     * @param UriInterface|string $uri
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest($method, $uri) : ServerRequestInterface
    {
        if (!($uri instanceof UriInterface)) {
            $uri = $this->uriFactory->createUri($uri);
        }
        $body = $this->streamFactory->createStream();

        return new ServerRequest($method, $uri, $body);
    }

    /**
     * Create a new server request from server variables.
     *
     * @param array $server Typically $_SERVER or similar structure.
     *
     * @return ServerRequestInterface
     *
     * @throws \InvalidArgumentException If no valid method or URI can be determined.
     */
    public function createServerRequestFromArray(array $server) : ServerRequestInterface
    {
        $method = $this->getMethodFromArray($server);

        $uri = $this->uriFactory->createUri($this->getUriStringFromArray($server));

        $headers = $this->createHeadersFromArray($server);

        $cookieHeader = (isset($headers['COOKIE']) ? $headers['COOKIE'] : ['']);
        $cookies = $this->getCookiesFromHeader($cookieHeader[0]);

        $body = $this->streamFactory->createStreamFromFile('php://input');

        return new ServerRequest($method, $uri, $body, $headers, $server, [], $cookies);
    }

    /**
     * get HTTP Method from server array
     *
     * @param $server
     *
     * @return string
     */
    protected function getMethodFromArray($server) : string
    {
        return (isset($server['REQUEST_METHOD'])) ? strtoupper($server['REQUEST_METHOD']) : 'GET';
    }

    /**
     * get Uri from server array
     *
     * @param $server
     *
     * @return string
     */
    protected function getUriStringFromArray($server) : string
    {
        $uri = $this->getSchemeFromArray($server) . '://';

        $auth = $this->getAuthorisationFromArray($server);
        if ($auth != '') {
            $uri .= $auth . '@';
        }

        $uri .= $this->getHostFromArray($server) . ':' . $this->getPortFromArray($server);

        $uri .= $this->getRequestUriFromArray($server);

        return $uri;
    }

    /**
     * get scheme from server array
     *
     * @param $server
     *
     * @return string
     */
    protected function getSchemeFromArray($server) : string
    {
        $https = (isset($server['HTTPS'])) ? $server['HTTPS'] : '';

        return (!empty($https) && $https !== 'off') ? 'https' : 'http';
    }

    /**
     * get authorisation string from server array
     *
     * @param $server
     *
     * @return string
     */
    protected function getAuthorisationFromArray($server) : string
    {
        $authorisation = '';
        if (isset($server['PHP_AUTH_USER'])) {
            $authorisation = $server['PHP_AUTH_USER'];
            if (isset($server['PHP_AUTH_PW'])) {
                $authorisation .= ':' . $server['PHP_AUTH_PW'];
            }
        }

        return $authorisation;
    }

    /**
     * get hostname from server array
     *
     * @param $server
     *
     * @return string
     */
    protected function getHostFromArray($server) : string
    {
        switch (true) {
            case isset($server['HTTP_HOST']):
                return $server['HTTP_HOST'];
                break;
            case isset($server['SERVER_NAME']):
                return $server['SERVER_NAME'];
                break;
            case isset($server['SERVER_ADDR']):
                return $server['SERVER_ADDR'];
                break;
            default:
                return 'undefined';
        }
    }

    /**
     * get port from server array
     *
     * @param $server
     *
     * @return int
     */
    protected function getPortFromArray($server) : int
    {
        return (isset($server['SERVER_PORT'])) ? (int) $server['SERVER_PORT'] : 80;
    }

    /**
     * get request uri from server array
     *
     * @param $server
     *
     * @return string
     */
    protected function getRequestUriFromArray($server) : string
    {
        return (isset($server['REQUEST_URI'])) ? $server['REQUEST_URI'] : '/';
    }

    /**
     * create array of request headers from server array
     *
     * @param $server
     *
     * @return array|false
     */
    protected function createHeadersFromArray($server)
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (!$headers) {
            $headers = [];
        }
        $headers = array_change_key_case($headers, CASE_UPPER);

        foreach ($server as $name => $value) {
            $name = strtoupper($name);

            if ($name === 'HTTP_CONTENT_LENGTH') {
                continue;
            }

            switch (true) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case strpos($name, 'HTTP_') === 0:
                    $name = substr($name, 5);
                // fallthrough
                case in_array($name, Message::SPECIAL_HEADER):
                    $name = str_replace('_', '-', strtoupper($name));
                    $headers[$name] = array_map('trim', explode(',', $value));
                    break;
            }
        }

        return $headers;
    }

    /**
     * create cookie array from server array
     *
     * @param string $value
     *
     * @return array
     */
    protected function getCookiesFromHeader(string $value) : array
    {
        $cookies = [];

        $value = rtrim($value, "\r\n");
        $pieces = preg_split('@[;]\s*@', $value);
        foreach ($pieces as $cookie) {
            $cookie = explode('=', $cookie, 2);

            if (count($cookie) === 2) {
                $key = urldecode($cookie[0]);
                $value = urldecode($cookie[1]);

                if (!isset($cookies[$key])) {
                    $cookies[$key] = $value;
                }
            }
        }

        return $cookies;
    }
}
