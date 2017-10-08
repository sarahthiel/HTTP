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
use Psr\Http\Message\ServerRequestInterface;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\ServerRequest\ServerRequest;
use sebastianthiel\HTTP\Uri\Uri;

class ServerRequestTest extends Unit
{
    /**
     * @param $params
     *
     * @return array
     */
    public function getServerParamsMock($params) : array
    {
        return array_merge([], $params);
    }

    /**
     * @param array $options
     *
     * @return ServerRequestInterface
     */
    public function requestFactory(array $options = []) : ServerRequestInterface
    {
        $options = array_merge(
            [
                'method'         => 'GET',
                'body'           => (new StreamFactory())->createStream(),
                'headers'        => [],
                'version'        => '1.1',
                'uri_params'     => [],
                'server_params'  => [],
                'query_params'   => [],
                'cookie_params'  => [],
                'uploaded_files' => [],
                'attributes'     => []
            ],
            $options
        );
        $options['server_params'] = $this->getServerParamsMock($options['server_params']);
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

        return new ServerRequest(
            $options['method'],
            $options['uri'],
            $options['body'],
            $options['headers'],
            $options['server_params'],
            $options['query_params'],
            $options['cookie_params'],
            $options['uploaded_files'],
            $options['attributes'],
            $options['version']
        );
    }

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testGetServerParams()
    {
        $request = $this->requestFactory(['server_params' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getServerParams()['foo']);
    }

    /**
     *
     */
    public function testGetCookieParams()
    {
        $request = $this->requestFactory(['cookie_params' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getCookieParams()['foo']);
    }

    /**
     *
     */
    public function testWithCookieParams()
    {
        $request = $this->requestFactory();
        $request = $request->withCookieParams(['foo' => 'baz']);
        $this->assertEquals('baz', $request->getCookieParams()['foo']);
    }

    /**
     *
     */
    public function testGetQueryParams()
    {
        $request = $this->requestFactory(['query_params' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getQueryParams()['foo']);
    }

    /**
     *
     */
    public function testWithQueryParams()
    {
        $request = $this->requestFactory();
        $request = $request->withQueryParams(['foo' => 'baz']);
        $this->assertEquals('baz', $request->getQueryParams()['foo']);
    }

    /**
     *
     */
    public function testGetUploadedFiles()
    {
        $request = $this->requestFactory(['uploaded_files' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getUploadedFiles()['foo']);
    }

    /**
     *
     */
    public function testWithUploadedFiles()
    {
        $request = $this->requestFactory();
        $request = $request->withUploadedFiles(['foo' => 'baz']);
        $this->assertEquals('baz', $request->getUploadedFiles()['foo']);
    }

    /**
     *
     */
    public function testParsedBody()
    {
        $request = $this->requestFactory();
        $request = $request->withParsedBody(['Foo']);
        $this->assertEquals(['Foo'], $request->getParsedBody());
    }

    /**
     *
     */
    public function testGetAttributes()
    {
        $request = $this->requestFactory(['attributes' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getAttributes()['foo']);
    }

    /**
     *
     */
    public function testGetAttribute()
    {
        $request = $this->requestFactory(['attributes' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getAttribute('foo'));
        $this->assertEquals('baz', $request->getAttribute('bar', 'baz'));
    }

    /**
     *
     */
    public function testWithAttribute()
    {
        $request = $this->requestFactory(['attributes' => ['foo' => 'bar']]);
        $this->assertEquals(null, $request->getAttribute('bar', null));
        $request = $request->withAttribute('bar', 'baz');
        $this->assertEquals('baz', $request->getAttribute('bar', null));
        $request = $request->withAttribute('bar', 'qux');
        $this->assertEquals('qux', $request->getAttribute('bar', null));
    }

    /**
     *
     */
    public function testWithoutAttribute()
    {
        $request = $this->requestFactory(['attributes' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $request->getAttribute('foo', null));
        $request = $request->withoutAttribute('foo');
        $this->assertEquals(null, $request->getAttribute('foo', null));
    }

    /**
     *
     */
    public function testAddHeaders()
    {
        $request = $this->requestFactory(['headers' => ['foo' => 'bar', 'bar' => ['baz']]]);

        $this->assertEquals(['bar'], $request->getHeader('foo'));
        $this->assertEquals(['baz'], $request->getHeader('bar'));
    }
}
