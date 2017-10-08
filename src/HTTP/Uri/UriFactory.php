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

use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri($uri = '') : UriInterface
    {
        $parts = parse_url($uri);

        if (!$parts) {
            throw new \InvalidArgumentException(sprintf('Unable to parse URI: "%s"', $uri));
        }

        return new Uri(
            isset($parts['scheme']) ? $parts['scheme'] : '',
            isset($parts['user']) ? $parts['user'] : '',
            isset($parts['pass']) ? $parts['pass'] : '',
            isset($parts['host']) ? $parts['host'] : '',
            isset($parts['port']) ? $parts['port'] : null,
            isset($parts['path']) ? $parts['path'] : '/',
            isset($parts['query']) ? $parts['query'] : '',
            isset($parts['fragment']) ? $parts['fragment'] : ''
        );
    }
}
