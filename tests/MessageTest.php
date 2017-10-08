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
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\Message\Message;

class MessageTest extends Unit
{
    /**
     * @return MessageInterface
     */
    public function messageFactory() : MessageInterface
    {
        return new Message(new StreamFactory());
    }

    /**
     * @param string $content
     *
     * @return StreamInterface
     */
    public function streamFactory($content = '') : StreamInterface
    {
        return (new StreamFactory())->createStream($content);
    }


    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testGetProtocolVersion()
    {
        $message = $this->messageFactory();
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

    /**
     *
     */
    public function testWithProtocolVersion()
    {
        $message = $this->messageFactory()->withProtocolVersion('2.0');
        $this->assertAttributeEquals('2.0', 'protocolVersion', $message);
        $this->assertEquals('2.0', $message->getProtocolVersion());
    }

    /**
     *
     */
    public function testGetHeaders()
    {
        $message = $this->messageFactory();
        $message = $message->withHeader('X-UnitTest', 'foo');

        $this->assertEquals(['X-UnitTest' => ['foo']], $message->getHeaders());
    }

    /**
     *
     */
    public function testHasHeader()
    {
        $message = $this->messageFactory();
        $this->assertEquals(false, $message->hasHeader('X-UnitTest'));

        $message = $message->withHeader('X-UnitTest', 'foo');

        $this->assertEquals(true, $message->hasHeader('X-UnitTest'));
    }

    /**
     *
     */
    public function testGetHeader()
    {
        $message = $this->messageFactory();
        $message = $message->withHeader('X-UnitTest', 'foo');

        $this->assertEquals([], $message->getHeader('X-Foo'));
        $this->assertEquals(['foo'], $message->getHeader('X-UnitTest'));
    }

    /**
     *
     */
    public function testGetHeaderLine()
    {
        $message = $this->messageFactory();
        $message = $message->withHeader('X-UnitTest', ['foo', 'bar', 'baz']);

        $this->assertEquals('', $message->getHeaderline('X-Foo'));
        $this->assertEquals('foo,bar,baz', $message->getHeaderline('X-UnitTest'));
    }

    /**
     *
     */
    public function testWithHeader()
    {
        $message = $this->messageFactory();
        $message = $message->withHeader('X-UnitTest', ['foo']);
        $message = $message->withHeader('X-UnitTest', 'bar');

        $this->assertEquals(['bar'], $message->getHeader('X-UnitTest'));
    }

    /**
     *
     */
    public function testWithAddedHeader()
    {
        $message = $this->messageFactory();
        $message = $message->withHeader('X-UnitTest', ['foo']);
        $message = $message->withAddedHeader('X-UnitTest', 'bar');

        $this->assertEquals(['foo', 'bar'], $message->getHeader('X-UnitTest'));
    }

    /**
     *
     */
    public function testWithoutHeader()
    {
        $message = $this->messageFactory();
        $message = $message->withHeader('X-UnitTest', ['foo']);
        $this->assertEquals(true, $message->hasHeader('X-UnitTest'));
        $message = $message->withoutHeader('X-UnitTest');
        $this->assertEquals(false, $message->hasHeader('X-UnitTest'));
    }

    /**
     *
     */
    public function testGetBody()
    {
        $message = $this->messageFactory();
        $this->assertInstanceOf(StreamInterface::class, $message->getBody());
    }

    /**
     *
     */
    public function testWithBody()
    {
        $body = $this->streamFactory('Test');
        $message = $this->messageFactory()->withBody($body);
        $this->assertSame($body, $message->getBody());
    }
}
