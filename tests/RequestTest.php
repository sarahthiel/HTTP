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
use Psr\Http\Message\RequestInterface;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\Request\Request;
use sebastianthiel\HTTP\Uri\Uri;

class RequestTest extends Unit
{

    /**
     * @param array $options
     *
     * @return RequestInterface
     */
    public function requestFactory(array $options = []) : RequestInterface
    {
        $options = array_merge(
            [
                'method'     => 'GET',
                'body'       => (new StreamFactory())->createStream(),
                'headers'    => [],
                'version'    => '1.1',
                'uri_params' => []
            ],
            $options
        );

        if (!isset($options['uri'])) {
            $params = array_merge(
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
                $options['uri_params']
            );

            $options['uri'] = new Uri(
                $params['scheme'],
                $params['user'],
                $params['password'],
                $params['host'],
                $params['port'],
                $params['path'],
                $params['query'],
                $params['fragment']
            );
        }

        return new Request($options['method'], $options['uri'], $options['body'], $options['headers'], $options['version']);
    }

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::getRequestTarget
     */
    public function testGetRequestTarget()
    {
        $request = $this->requestFactory(['uri_params' => ['path' => '/foo', 'query' => 'bar=baz']]);
        $this->assertEquals('/foo?bar=baz', $request->getRequestTarget());
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::withRequestTarget
     * @covers \sebastianthiel\HTTP\Request\Request::getRequestTarget
     */
    public function testWithRequestTarget()
    {
        $request = $this->requestFactory();
        $request = $request->withRequestTarget('/Foo/Bar');
        $this->assertEquals('/Foo/Bar', $request->getRequestTarget());
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::getMethod
     */
    public function testGetMethod()
    {
        $request = $this->requestFactory(['method' => 'POST']);
        $this->assertEquals('POST', $request->getMethod());
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::withMethod
     */
    public function testWithMethod()
    {
        $request = $this->requestFactory(['method' => 'POST']);
        $request = $request->withMethod('DELETE');
        $this->assertEquals('DELETE', $request->getMethod());
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::withMethod
     *
     * @expectedException \sebastianthiel\HTTP\Exception\InvalidMethodException
     */
    public function testWithInvalidMethod()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\InvalidMethodException::class);

        $request = $this->requestFactory(['method' => 'POST']);
        $request->withMethod('FOO');
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::getUri
     */
    public function testGetUri()
    {
        $request = $this->requestFactory(['uri_params' => ['path' => '/foo']]);
        $this->assertEquals('/foo', $request->getUri()->getPath());
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::withUri
     */
    public function testWithUri()
    {
        $request = $this->requestFactory(['uri_params' => ['scheme' => 'https', 'port' => null, 'host' => 'example.com']]);
        $uri = $request->getUri();
        $request = $request->withUri($uri->withHost('192.0.2.1'));
        $this->assertEquals('192.0.2.1', $request->getUri()->getHost());
        $this->assertEquals('192.0.2.1:443', $request->getHeader('host')[0]);
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::withUri
     */
    public function testWithUriWithNoHost()
    {
        $request = $this->requestFactory(['uri_params' => ['scheme' => 'https', 'port' => null, 'host' => 'example.com']]);
        $uri = $request->getUri();
        $request = $request->withUri($uri->withHost(''));
        $this->assertEquals('', $request->getUri()->getHost());
        $this->assertEquals('example.com:443', $request->getHeader('host')[0]);
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::withUri
     */
    public function testWithUriPreserveHost()
    {
        $request = $this->requestFactory(['uri_params' => ['scheme' => 'https', 'port' => null, 'host' => 'example.com']]);
        $uri = $request->getUri();
        $request = $request->withUri($uri->withHost('192.0.2.1'), true);
        $this->assertEquals('192.0.2.1', $request->getUri()->getHost());
        $this->assertEquals('example.com:443', $request->getHeader('host')[0]);
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::__construct
     *
     * @expectedException \sebastianthiel\HTTP\Exception\InvalidMethodException
     */
    public function testInvalidMethodConstructor()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\InvalidMethodException::class);

        $this->requestFactory(['method' => 'foo']);
    }

    /**
     * @covers \sebastianthiel\HTTP\Request\Request::__construct
     * @covers \sebastianthiel\HTTP\Request\Request::getRequestTarget
     * @covers \sebastianthiel\HTTP\Message\Message::addHeaders
     */
    public function testMinimalConstructor()
    {
        $options = [
            'method'     => 'GET',
            'body'       => (new StreamFactory())->createStream(),
            'headers'    => [],
            'version'    => '1.1',
            'uri_params' => [
                'scheme'   => '',
                'user'     => '',
                'password' => '',
                'host'     => '',
                'port'     => null,
                'path'     => '',
                'query'    => '',
                'fragment' => ''
            ]
        ];
        $request = $this->requestFactory($options);

        $this->assertEquals('/', $request->getRequestTarget());
        $this->assertEquals('', $request->getUri()->getHost());
    }
}
