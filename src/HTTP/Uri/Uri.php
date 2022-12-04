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

namespace sebastianthiel\HTTP\Uri;

use Psr\Http\Message\UriInterface;
use sebastianthiel\HTTP\Exception\UriInvalidArgumentException;

class Uri implements UriInterface
{
    const PORTMAP = [
        'file'  => 0,
        'http'  => 80,
        'https' => 443,
        'ftp'   => 21,
        'scp'   => 22
    ];

    /**  @var string */
    protected $scheme;

    /** @var string */
    protected $user;

    /**  @var string */
    protected $password;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var string */
    protected $path;

    /**  @var string */
    protected $query;

    /** @var string */
    protected $fragment;

    /**
     * Uri constructor.
     *
     * @param string   $scheme
     * @param string   $user
     * @param string   $password
     * @param string   $host
     * @param null|int $port
     * @param string   $path
     * @param string   $query
     * @param string   $fragment
     */
    public function __construct(
        $scheme = '',
        $user = '',
        $password = '',
        $host = '',
        $port = null,
        $path = '/',
        $query = '',
        $fragment = ''
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->user = $this->filterUser($user);
        $this->password = $this->filterPassword($password);
        $this->host = $this->filterHost($host);
        $this->port = $this->filterPort($port);
        $this->path = $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterFragment($fragment);
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * @return string The URI scheme.
     */
    public function getScheme() : string
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority() : string
    {
        $userInfo = $this->getUserInfo();
        $port = (string) $this->getPort();

        $authority = $this->getHost();

        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }

        if ($port !== null && $this->getDefaultPort() != $port) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo() : string
    {
        return $this->user . ($this->user !== '' && $this->password !== '' ? ':' . $this->password : '');
    }

    /**
     * Retrieve the host component of the URI.
     *
     * @return string The URI host.
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        if ($this->port) {
            return $this->port;
        }

        return $this->getDefaultPort();
    }

    /**
     * gets the default port for teh current scheme
     *
     * @return mixed|null
     */
    protected function getDefaultPort()
    {
        $scheme = strtolower($this->getScheme());

        return (
            $scheme != ''
            && isset(static::PORTMAP[$scheme])
            && (int) static::PORTMAP[$scheme] > 0
        )
            ? static::PORTMAP[$scheme]
            : null;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * @return string The URI path.
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * @return string The URI query string.
     */
    public function getQuery() : string
    {
        return $this->query;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * @return string The URI fragment.
     */
    public function getFragment() : string
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     *
     * @return UriInterface A new instance with the specified scheme.
     * @throws UriInvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme) : UriInterface
    {
        $scheme = $this->filterScheme($scheme);
        $uri = clone $this;
        $uri->scheme = $scheme;

        return $uri;
    }

    /**
     * validate and normalize a scheme
     *
     * @param $scheme
     *
     * @return string
     * @throws UriInvalidArgumentException
     */
    protected function filterScheme($scheme) : string
    {
        if (!(is_string($scheme) || (is_object($scheme) && method_exists($scheme, '__toString')))) {        
            throw new UriInvalidArgumentException('Scheme must be a string');
        }

        $scheme = str_replace('://', '', strtolower((string) $scheme));

        if ($scheme !== '' && !isset(static::PORTMAP[$scheme])) {
            throw new UriInvalidArgumentException(sprintf('unknown scheme "%s"', $scheme));
        }

        return $scheme;
    }

    /**
     * Return an instance with the specified user information.
     *
     * @param string $user
     * @param string $password
     *
     * @return UriInterface A new instance with the specified user information.
     * @throws UriInvalidArgumentException
     */
    public function withUserInfo($user, $password = '') : UriInterface
    {
        $uri = clone $this;
        $uri->user = $this->filterUser($user);
        $uri->password = $this->filterPassword($password);

        return $uri;
    }

    /**
     * validate and normalize a username
     *
     * @param $user
     *
     * @return string
     * @throws UriInvalidArgumentException
     */
    protected function filterUser($user) : string
    {
        if (!(is_string($user) || (is_object($user) && method_exists($user, '__toString')))) {
            throw new UriInvalidArgumentException('User must be a string');
        }

        return (string) $user;
    }

    /**
     * validate and normalize a password
     *
     * @param $password
     *
     * @return string
     * @throws UriInvalidArgumentException
     */
    protected function filterPassword($password) : string
    {
        if (!(is_string($password) || (is_object($password) && method_exists($password, '__toString')))) {
            throw new UriInvalidArgumentException('Password must be a string');
        }

        return (string) $password;
    }

    /**
     * Return an instance with the specified host.
     *
     * @param string $host The hostname to use with the new instance.
     *
     * @return UriInterface A new instance with the specified host.
     * @throws UriInvalidArgumentException for invalid hostnames.
     */
    public function withHost($host) : UriInterface
    {
        $host = $this->filterHost($host);
        $uri = clone $this;
        $uri->host = $host;

        return $uri;
    }

    /**
     * validate and normalize hostname
     *
     * @param $host
     *
     * @return string
     * @throws UriInvalidArgumentException
     */
    protected function filterHost($host) : string
    {
        if (!(is_string($host) || (is_object($host) && method_exists($host, '__toString')))) {
            throw new UriInvalidArgumentException('Host must be a string');
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $host = '[' . $host . ']';
        }

        return strtolower($host);
    }

    /**
     * Return an instance with the specified port.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information.
     *
     * @return UriInterface A new instance with the specified port.
     * @throws UriInvalidArgumentException for invalid ports.
     */
    public function withPort($port) : UriInterface
    {
        $port = $this->filterPort($port);
        $uri = clone $this;
        $uri->port = $port;

        return $uri;
    }

    /**
     * validate and normalize port
     *
     * @param $port
     *
     * @return int|null
     * @throws UriInvalidArgumentException
     */
    protected function filterPort($port)
    {
        if ($port === null) {
            return null;
        }

        $port = (int) $port;
        if ($port < 1 || $port > 65535) {
            throw new UriInvalidArgumentException(sprintf('Invalid port: %d.', $port));
        }

        return $port;
    }

    /**
     * Return an instance with the specified path.
     *
     * @param string $path The path to use with the new instance.
     *
     * @return UriInterface A new instance with the specified path.
     * @throws UriInvalidArgumentException for invalid paths.
     */
    public function withPath($path) : UriInterface
    {

        $path = $this->filterPath($path);
        $uri = clone $this;
        $uri->path = $path;

        return $uri;
    }

    /**
     * validate and normalize path
     *
     * @param $path
     *
     * @return string
     * @throws UriInvalidArgumentException
     */
    protected function filterPath($path) : string
    {
        if (!(is_string($path) || (is_object($path) && method_exists($path, '__toString')))) {        
            throw new UriInvalidArgumentException('Path must be a string');
        }
        $path = empty($path) ? '/' : $path;

        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~ . !\$&\'\(\)\*\+,;=%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    /**
     * Return an instance with the specified query string.
     *
     * @param string $query The query string to use with the new instance.
     *
     * @return UriInterface A new instance with the specified query string.
     * @throws UriInvalidArgumentException for invalid query strings.
     */
    public function withQuery($query) : UriInterface
    {
        $query = $this->filterQuery($query);
        $uri = clone $this;
        $uri->query = $query;

        return $uri;
    }

    /**
     * validate and normalize query string
     *
     * @param $query
     *
     * @return mixed
     * @throws UriInvalidArgumentException
     */
    protected function filterQuery($query)
    {
        if (!(is_string($query) || (is_object($query) && method_exists($query, '__toString')))) {        
            throw new UriInvalidArgumentException('Query must be a string');
        }

        $query = ltrim($query, '?');

        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~ . !\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     *
     * @return UriInterface A new instance with the specified fragment.
     * @throws UriInvalidArgumentException
     */
    public function withFragment($fragment) : UriInterface
    {
        $fragment = $this->filterFragment($fragment);
        $uri = clone $this;
        $uri->fragment = $fragment;

        return $uri;
    }

    /**
     * validate and normalize query string
     *
     * @param $fragment
     *
     * @return mixed
     * @throws UriInvalidArgumentException
     */
    protected function filterFragment($fragment)
    {
        if (!(is_string($fragment) || (is_object($fragment) && method_exists($fragment, '__toString')))) {        
            throw new UriInvalidArgumentException('Fragment must be a string');
        }
        $fragment = ltrim($fragment, '#');

        return $this->filterQuery($fragment);
    }

    /**
     * Return the string representation as a URI reference.
     *
     * @return string
     */
    public function __toString() : string
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = '/' . ltrim($this->getPath(), '/');
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $uri = '';

        if ($scheme != '') {
            $uri .= $scheme . '://';
        }

        if ($authority != '') {
            $uri .= $authority;
        }

        $uri .= $path;

        if ($query != '') {
            $uri .= '?' . $query;
        }

        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }
}
