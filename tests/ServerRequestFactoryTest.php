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
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use sebastianthiel\HTTP\ServerRequest\ServerRequestFactory;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\UploadedFile\UploadedFileFactory;
use sebastianthiel\HTTP\Uri\UriFactory;

class ServerRequestFactoryTest extends Unit
{

    /**
     * @return ServerRequestFactoryInterface
     */
    public function getFactory() : ServerRequestFactoryInterface
    {
        $streamFactory = new StreamFactory();

        return new ServerRequestFactory(new UriFactory(), $streamFactory, new UploadedFileFactory($streamFactory));
    }

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testCreateServerRequest()
    {
        $request = $this->getFactory()->createServerRequest('PUT', 'http://example.com');
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('example.com', $request->getUri()->getHost());
    }

    /**
     *
     */
    public function testCreateServerRequestFromArrayWithServerNameHeader()
    {
        $server = [
            'REQUEST_METHOD'       => 'POST',
            'HTTPS'                => 1,
            'PHP_AUTH_USER'        => 'Alice',
            'PHP_AUTH_PW'          => 'secret',
            'SERVER_NAME'          => 'example.com',
            'SERVER_PORT'          => 8443,
            'REQUEST_URI'          => '/foo?bar=baz',
            'HTTP_CONTENT_LENGTH'  => 0,
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'CONTENT_TYPE'         => 'text/html',
            'COOKIE'               => 'foo=1; bar=baz'
        ];

        $request = $this->getFactory()->createServerRequestFromArray($server);

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https', $request->getUri()->getScheme());
        $this->assertEquals('Alice:secret', $request->getUri()->getUserInfo());
        $this->assertEquals('example.com', $request->getUri()->getHost());
        $this->assertEquals(8443, $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals(false, $request->hasHeader('http-content-length'));
        $this->assertEquals(['gzip', 'deflate'], $request->getHeader('accept-encoding'));
        $this->assertEquals(['text/html'], $request->getHeader('content-type'));
        $this->assertEquals(['foo' => 1, 'bar' => 'baz'], $request->getCookieParams());
    }

    /**
     *
     */
    public function testCreateServerRequestFromArrayWithHttpHostHeader()
    {
        $server = [
            'REQUEST_METHOD'       => 'POST',
            'HTTPS'                => 1,
            'PHP_AUTH_USER'        => 'Alice',
            'PHP_AUTH_PW'          => 'secret',
            'HTTP_HOST'            => 'example.com',
            'SERVER_PORT'          => 8443,
            'REQUEST_URI'          => '/foo?bar=baz',
            'HTTP_CONTENT_LENGTH'  => 0,
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'CONTENT_TYPE'         => 'text/html',
            'COOKIE'               => 'foo=1; bar=baz'
        ];

        $request = $this->getFactory()->createServerRequestFromArray($server);

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https', $request->getUri()->getScheme());
        $this->assertEquals('Alice:secret', $request->getUri()->getUserInfo());
        $this->assertEquals('example.com', $request->getUri()->getHost());
        $this->assertEquals(8443, $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals(false, $request->hasHeader('http-content-length'));
        $this->assertEquals(['gzip', 'deflate'], $request->getHeader('accept-encoding'));
        $this->assertEquals(['text/html'], $request->getHeader('content-type'));
        $this->assertEquals(['foo' => 1, 'bar' => 'baz'], $request->getCookieParams());
    }

    /**
     *
     */
    public function testCreateServerRequestFromArrayWithServerAddrHeader()
    {
        $server = [
            'REQUEST_METHOD'       => 'POST',
            'HTTPS'                => 1,
            'PHP_AUTH_USER'        => 'Alice',
            'PHP_AUTH_PW'          => 'secret',
            'SERVER_ADDR'          => '192.2.0.1',
            'SERVER_PORT'          => 8443,
            'REQUEST_URI'          => '/foo?bar=baz',
            'HTTP_CONTENT_LENGTH'  => 0,
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'CONTENT_TYPE'         => 'text/html',
            'COOKIE'               => 'foo=1; bar=baz'
        ];

        $request = $this->getFactory()->createServerRequestFromArray($server);

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https', $request->getUri()->getScheme());
        $this->assertEquals('Alice:secret', $request->getUri()->getUserInfo());
        $this->assertEquals('192.2.0.1', $request->getUri()->getHost());
        $this->assertEquals(8443, $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals(false, $request->hasHeader('http-content-length'));
        $this->assertEquals(['gzip', 'deflate'], $request->getHeader('accept-encoding'));
        $this->assertEquals(['text/html'], $request->getHeader('content-type'));
        $this->assertEquals(['foo' => 1, 'bar' => 'baz'], $request->getCookieParams());
    }

    /**
     *
     */
    public function testCreateServerRequestFromArrayWithNoHostname()
    {
        $server = [
            'REQUEST_METHOD'       => 'POST',
            'HTTPS'                => 1,
            'PHP_AUTH_USER'        => 'Alice',
            'PHP_AUTH_PW'          => 'secret',
            'REQUEST_URI'          => '/foo?bar=baz',
            'HTTP_CONTENT_LENGTH'  => 0,
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'CONTENT_TYPE'         => 'text/html',
            'COOKIE'               => 'foo=1; bar=baz'
        ];

        $request = $this->getFactory()->createServerRequestFromArray($server);

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https', $request->getUri()->getScheme());
        $this->assertEquals('Alice:secret', $request->getUri()->getUserInfo());
        $this->assertEquals('undefined', $request->getUri()->getHost());
        $this->assertEquals(80, $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals('bar=baz', $request->getUri()->getQuery());
        $this->assertEquals(false, $request->hasHeader('http-content-length'));
        $this->assertEquals(['gzip', 'deflate'], $request->getHeader('accept-encoding'));
        $this->assertEquals(['text/html'], $request->getHeader('content-type'));
        $this->assertEquals(['foo' => 1, 'bar' => 'baz'], $request->getCookieParams());
    }
}
