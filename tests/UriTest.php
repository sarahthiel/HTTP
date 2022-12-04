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

namespace sebastianthiel\HTTP\Tests;

use Codeception\Test\Unit;
use Psr\Http\Message\UriInterface;
use sebastianthiel\HTTP\Uri\Uri;

class UriTest extends Unit
{
    /**
     * @param array $parameter
     *
     * @return UriInterface
     */
    public function uriFactory(array $parameter = []) : UriInterface
    {
        $parameter = array_merge(
            [
                'scheme'   => 'http',
                'user'     => 'alice',
                'password' => 'secret',
                'host'     => 'example.com',
                'port'     => 80,
                'path'     => '/foo/bar',
                'query'    => 'baz=42',
                'fragment' => 'qux'
            ],
            $parameter
        );

        return new Uri(
            $parameter['scheme'],
            $parameter['user'],
            $parameter['password'],
            $parameter['host'],
            $parameter['port'],
            $parameter['path'],
            $parameter['query'],
            $parameter['fragment']
        );
    }

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testGetScheme()
    {
        $this->assertEquals('http', $this->uriFactory(['scheme' => 'http'])->getScheme());
    }

    /**
     *
     */
    public function testWithScheme()
    {
        $this->assertEquals('https', $this->uriFactory(['scheme' => 'http'])->withScheme('https')->getScheme());
    }

    /**
     *
     */
    public function testWithSchemeRemovesSuffix()
    {
        $this->assertEquals('file', $this->uriFactory(['scheme' => 'http'])->withScheme('file://')->getScheme());
    }

    /**
     *
     */
    public function testWithSchemeEmpty()
    {
        $this->assertEquals('', $this->uriFactory(['scheme' => 'http'])->withScheme('')->getScheme());
    }

    /**
     * @return array
     */
    public function invalidSchemesProvider() : array
    {
        return [
            'foo'   => ['FOO'],
            'bool'  => [true],
            'array' => [['http']],
            'null'  => [null]
        ];
    }

    /**
     * @dataProvider invalidSchemesProvider
     *
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     *
     * @param $scheme
     */
    public function testWithInvalidScheme($scheme)
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withScheme($scheme);
    }

    /**
     * @return array
     */
    public function validSchemesProvider() : array
    {
        return [
            'file'  => ['file'],
            'http'  => ['http'],
            'https' => ['https'],
            'ftp'   => ['ftp'],
            'scp'   => ['scp'],
        ];
    }

    /**
     * @dataProvider validSchemesProvider
     *
     * @param $scheme
     */
    public function testValidSchemes($scheme)
    {
        $uri = $this->uriFactory()->withScheme($scheme);

        $this->assertEquals($scheme, $uri->getScheme());
    }

    /**
     * @return array
     */
    public function authorityProvider() : array
    {
        return [
            'with user, no port'           => [
                ['user' => 'alice', 'password' => '', 'host' => 'example.com', 'port' => null, 'scheme' => 'http'],
                'alice@example.com'
            ],
            'with user, standard port'     => [
                ['user' => 'alice', 'password' => '', 'host' => 'example.com', 'port' => 80, 'scheme' => 'http'],
                'alice@example.com'
            ],
            'with user, non standard port' => [
                ['user' => 'alice', 'password' => '', 'host' => 'example.com', 'port' => 443, 'scheme' => 'http'],
                'alice@example.com:443'
            ],

            'with user, with password, no port'           => [
                ['user' => 'alice', 'password' => 'secret', 'host' => 'example.com', 'port' => null, 'scheme' => 'http'],
                'alice:secret@example.com'
            ],
            'with user, with password, standard port'     => [
                ['user' => 'alice', 'password' => 'secret', 'host' => 'example.com', 'port' => 80, 'scheme' => 'http'],
                'alice:secret@example.com'
            ],
            'with user, with password, non standard port' => [
                ['user' => 'alice', 'password' => 'secret', 'host' => 'example.com', 'port' => 443, 'scheme' => 'http'],
                'alice:secret@example.com:443'
            ],

            'with password, no port'           => [
                ['user' => '', 'password' => 'secret', 'host' => 'example.com', 'port' => null, 'scheme' => 'http'],
                'example.com'
            ],
            'with password, standard port'     => [
                ['user' => '', 'password' => 'secret', 'host' => 'example.com', 'port' => 80, 'scheme' => 'http'],
                'example.com'
            ],
            'with password, non standard port' => [
                ['user' => '', 'password' => 'secret', 'host' => 'example.com', 'port' => 443, 'scheme' => 'http'],
                'example.com:443'
            ],

            'no port'           => [
                ['user' => '', 'password' => '', 'host' => 'example.com', 'port' => null, 'scheme' => 'http'],
                'example.com'
            ],
            'standard port'     => [
                ['user' => '', 'password' => '', 'host' => 'example.com', 'port' => 80, 'scheme' => 'http'],
                'example.com'
            ],
            'non standard port' => [
                ['user' => '', 'password' => '', 'host' => 'example.com', 'port' => 443, 'scheme' => 'http'],
                'example.com:443'
            ],

            'ipv4, no port'           => [
                ['user' => '', 'password' => '', 'host' => '192.0.2.1', 'port' => null, 'scheme' => 'http'],
                '192.0.2.1'
            ],
            'ipv4, standard port'     => [
                ['user' => '', 'password' => '', 'host' => '192.0.2.1', 'port' => 80, 'scheme' => 'http'],
                '192.0.2.1'
            ],
            'ipv4, non standard port' => [
                ['user' => '', 'password' => '', 'host' => '192.0.2.1', 'port' => 443, 'scheme' => 'http'],
                '192.0.2.1:443'
            ],

            'ipv6, no port'           => [
                ['user' => '', 'password' => '', 'host' => '2001:db8::dead:beef', 'port' => null, 'scheme' => 'http'],
                '[2001:db8::dead:beef]'
            ],
            'ipv6, standard port'     => [
                ['user' => '', 'password' => '', 'host' => '2001:db8::dead:beef', 'port' => 80, 'scheme' => 'http'],
                '[2001:db8::dead:beef]'
            ],
            'ipv6, non standard port' => [
                ['user' => '', 'password' => '', 'host' => '2001:db8::dead:beef', 'port' => 443, 'scheme' => 'http'],
                '[2001:db8::dead:beef]:443'
            ]
        ];
    }

    /**
     * @dataProvider authorityProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetAuthority($values, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->uriFactory($values)->getAuthority()
        );
    }

    /**
     * @return array
     */
    public function userInfoProvider() : array
    {
        return [
            'user'               => [
                ['user' => 'alice', 'password' => ''],
                'alice'
            ],
            'user and password,' => [
                ['user' => 'alice', 'password' => 'secret'],
                'alice:secret'
            ],
            'password only'      => [
                ['user' => '', 'password' => 'secret'],
                ''
            ],
            'empty'              => [
                ['user' => '', 'password' => ''],
                ''
            ]
        ];
    }

    /**
     * @dataProvider userInfoProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetUserInfo($values, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->uriFactory($values)->getUserInfo()
        );
    }

    /**
     *
     */
    public function testWithUserInfo()
    {
        $uri = $this->uriFactory(['user' => 'alice', 'password' => 'secret'])->withUserInfo('bob', 'password');

        $this->assertEquals('bob:password', $uri->getUserInfo());
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     */
    public function testInvalidUser()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withUserInfo(false);
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     */
    public function testInvalidPassword()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withUserInfo('alice', false);
    }

    /**
     * @return array
     */
    public function hostProvider()
    {
        return [
            'hostname' => [['host' => 'example.com'], 'example.com'],
            'ipv4'     => [['host' => '192.0.2.1'], '192.0.2.1'],
            'ipv6'     => [['host' => '2001:db8::dead:beef'], '[2001:db8::dead:beef]'],
            'ipv6reference'     => [['host' => '[2001:db8::dead:beef]'], '[2001:db8::dead:beef]']
        ];
    }

    /**
     * @dataProvider hostProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetHost($values, $expected)
    {
        $this->assertEquals($expected, $this->uriFactory($values)->getHost());
    }

    /**
     * @dataProvider hostProvider
     *
     * @param $values
     * @param $expected
     */
    public function testWithHost($values, $expected)
    {
        $uri = $this->uriFactory(['host' => ''])->withHost($values['host']);

        $this->assertEquals($expected, $uri->getHost());
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     */
    public function testInvalidHost()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withHost(false);
    }

    /**
     * @return array
     */
    public function portProvider() : array
    {
        return [
            'scheme, default port'   => [['scheme' => 'https', 'port' => 443], 443],
            'scheme, custom port'    => [['scheme' => 'https', 'port' => 8443], 8443],
            'scheme, no port'        => [['scheme' => 'https', 'port' => null], 443],
            'no scheme, custom port' => [['scheme' => '', 'port' => 80], 80],
            'file scheme, no port'   => [['scheme' => 'file', 'port' => null], null]
        ];
    }

    /**
     * @dataProvider portProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetPort($values, $expected)
    {
        $uri = $this->uriFactory($values);
        $this->assertEquals($expected, $uri->getPort());
    }

    /**
     *
     */
    public function testWithPort()
    {
        $uri = $this->uriFactory(['scheme' => 'file', 'port' => null])->withPort(8080);
        $this->assertEquals(8080, $uri->getPort());

        $uri = $this->uriFactory(['scheme' => 'file', 'port' => null])->withPort(null);
        $this->assertEquals(null, $uri->getPort());
    }

    /**
     * @return array
     */
    public function invalidPortProvider() : array
    {
        return [
            ['http'],
            [0],
            [65536],
        ];
    }

    /**
     * @dataProvider invalidPortProvider
     *
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     *
     * @param $port
     */
    public function testInvalidPort($port)
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withPort($port);
    }

    /**
     * @return array
     */
    public function pathProvider() : array
    {
        return [
            'empty path'    => [['path' => ''], ['internal' => '', 'external' => '/']],
            'absolute path' => [['path' => '/foo'], ['internal' => '/foo', 'external' => '/foo']],
            'relative path' => [['path' => 'foo'], ['internal' => 'foo', 'external' => 'foo']],
            'urlencode'     => [['path' => '/foo#bar/'], ['internal' => '/foo%23bar/', 'external' => '/foo%23bar/']],
            'double encode' => [['path' => '/foo%23bar/'], ['internal' => '/foo%23bar/', 'external' => '/foo%23bar/']],
        ];
    }

    /**
     * @dataProvider pathProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetPath($values, $expected)
    {
        $this->assertEquals($expected['external'], $this->uriFactory($values)->getPath());
    }

    /**
     * @dataProvider pathProvider
     *
     * @param $values
     * @param $expected
     */
    public function testWithPath($values, $expected)
    {
        $uri = $this->uriFactory(['path' => '.'])->withPath($values['path']);
        $this->assertEquals($expected['external'], $uri->getPath());
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     */
    public function testInvalidPath()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withPath(false);
    }

    /**
     * @return array
     */
    public function queryProvider() : array
    {
        return [
            'empty query'       => [['query' => ''], 'external' => ''],
            'one param'         => [['query' => 'foo=bar'], 'foo=bar'],
            'two params'        => [['query' => 'foo=bar&baz=qux'], 'foo=bar&baz=qux'],
            'one param encode'  => [['query' => 'foo=#bar'], 'foo=%23bar'],
            'two params encode' => [['query' => 'foo=#bar&baz=qux'], 'foo=%23bar&baz=qux'],
            'remove prefix'     => [['query' => '?foo=#bar&baz=qux'], 'foo=%23bar&baz=qux'],
        ];
    }


    /**
     * @dataProvider queryProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetQuery($values, $expected)
    {
        $this->assertEquals($expected, $this->uriFactory($values)->getQuery());
    }


    /**
     * @dataProvider queryProvider
     *
     * @param $values
     * @param $expected
     */
    public function testWithQuery($values, $expected)
    {
        $uri = $this->uriFactory(['query' => ''])->withQuery($values['query']);

        $this->assertEquals($expected, $uri->getQuery());
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     */
    public function testInvalidQuery()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withQuery(false);
    }

    /**
     * @return array
     */
    public function fragmentProvider() : array
    {
        return [
            'empty fragment' => [['fragment' => ''], 'external' => ''],
            'fragment'       => [['fragment' => 'foo-1'], 'foo-1'],
            'remove prefix'  => [['fragment' => '#foo-1'], 'foo-1'],
        ];
    }


    /**
     * @dataProvider fragmentProvider
     *
     * @param $values
     * @param $expected
     */
    public function testGetFragment($values, $expected)
    {
        $this->assertEquals($expected, $this->uriFactory($values)->getFragment());
    }

    /**
     * @dataProvider fragmentProvider
     *
     * @param $values
     * @param $expected
     */
    public function testWithFragment($values, $expected)
    {
        $uri = $this->uriFactory(['fragment' => ''])->withFragment($values['fragment']);

        $this->assertEquals($expected, $uri->getFragment());
    }

    /**
     * @return array
     */
    public function uriProvider() : array
    {
        return [
            [
                [
                    'scheme'   => 'http',
                    'user'     => 'alice',
                    'password' => 'secret',
                    'host'     => 'example.com',
                    'port'     => 8080,
                    'path'     => '/foo/bar',
                    'query'    => 'baz=42',
                    'fragment' => 'qux'
                ],
                'http://alice:secret@example.com:8080/foo/bar?baz=42#qux'
            ],
            [
                [
                    'scheme'   => 'http',
                    'user'     => 'alice',
                    'password' => 'secret',
                    'host'     => '2001:db8::dead:beef',
                    'port'     => 8080,
                    'path'     => '/foo/bar',
                    'query'    => 'baz=42',
                    'fragment' => 'qux'
                ],
                'http://alice:secret@[2001:db8::dead:beef]:8080/foo/bar?baz=42#qux'
            ]
        ];
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UriInvalidArgumentException
     */
    public function testInvalidFragment()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\UriInvalidArgumentException::class);

        $this->uriFactory()->withFragment(false);
    }


    /**
     * @dataProvider uriProvider
     *
     * @param $values
     * @param $expected
     */
    public function testToString($values, $expected)
    {
        $this->assertEquals($expected, (string) $this->uriFactory($values));
    }
}
