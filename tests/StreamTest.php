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
use Psr\Http\Message\StreamInterface;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\Stream\Stream;

class StreamTest extends Unit
{

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
     * @return array
     */
    public function fileModeProvider() : array
    {
        return [
            ['r', ['r' => true, 'w' => false]],
            ['r+', ['r' => true, 'w' => true]],
            ['r+b', ['r' => true, 'w' => true]],
            ['rb', ['r' => true, 'w' => false]],
            ['w', ['r' => false, 'w' => true]],
            ['w+', ['r' => true, 'w' => true]],
            ['w+b', ['r' => true, 'w' => true]],
            ['wb', ['r' => false, 'w' => true]]
        ];
    }

    /**
     * @dataProvider fileModeProvider
     *
     * @param string $mode
     * @param array  $expected
     */
    public function testModeAllowReading($mode, $expected)
    {
        $method = new \ReflectionMethod(Stream::class, 'modeAllowReading');
        $method->setAccessible(true);

        $stream = $this->streamFactory();
        $this->assertEquals($expected['r'], $method->invoke($stream, $mode));
    }

    /**
     * @dataProvider fileModeProvider
     *
     * @param string $mode
     * @param array  $expected
     */
    public function testModeAllowWriting($mode, $expected)
    {
        $method = new \ReflectionMethod(Stream::class, 'modeAllowWriting');
        $method->setAccessible(true);

        $stream = $this->streamFactory();
        $this->assertEquals($expected['w'], $method->invoke($stream, $mode));
    }

    /**
     *
     */
    public function testToString()
    {
        $stream = $this->streamFactory('Foo');
        $this->assertEquals('Foo', (string) $stream);
    }

    /**
     *
     */
    public function testGetSize()
    {
        $stream = $this->streamFactory('Foo');
        $this->assertEquals(3, $stream->getSize());
    }

    /**
     *
     */
    public function testDetach()
    {
        $stream = $this->streamFactory();
        $this->assertEquals(true, $stream->isReadable());
        $this->assertEquals(true, $stream->isWritable());
        $this->assertEquals(true, $stream->isSeekable());

        $resource = $stream->detach();
        $this->assertEquals(false, $stream->isReadable());
        $this->assertEquals(false, $stream->isWritable());
        $this->assertEquals(false, $stream->isSeekable());

        fclose($resource);
    }

    /**
     *
     */
    public function testFilePointer()
    {
        $stream = $this->streamFactory('Foo Bar Baz');
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
        $this->assertEquals(false, $stream->eof());
        $stream->seek(4);
        $this->assertEquals(4, $stream->tell());
        $this->assertEquals('Bar', $stream->read(3));
        $this->assertEquals(7, $stream->tell());

        $stream->seek(0, SEEK_END);
        $this->assertEquals(11, $stream->tell());
    }

    /**
     *
     */
    public function testWrite()
    {
        $stream = $this->streamFactory();
        $stream->rewind();
        $stream->write('Foo Bar');
        $stream->rewind();
        $this->assertEquals('Foo Bar', $stream->getContents());
    }

    /**
     *
     */
    public function testMetaData()
    {
        $stream = $this->streamFactory();
        $this->assertEquals('TEMP', ($stream->getMetadata())['stream_type']);
        $this->assertEquals('TEMP', $stream->getMetadata('stream_type'));
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testInvalidConstruction()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);
        new Stream(null);
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testTellException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);

                $stream = $this->streamFactory();
                $stream->detach();
        
                $stream->tell();
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testSeekException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);
                $stream = $this->streamFactory();
                $stream->detach();
        
                $stream->seek(0);
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testRewindException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);
                $stream = $this->streamFactory();
                $stream->detach();
        
                $stream->rewind();
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testWriteException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);
                $stream = $this->streamFactory();
                $stream->detach();
        
                $stream->write('Foo');
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testReadException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);
                $stream = $this->streamFactory();
                $stream->detach();
        
                $stream->read(1024);
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testReadLengthException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);

                $stream = $this->streamFactory();
                $stream->read(-1);
    }

    /**
     *
     */
    public function testReadZeroLength()
    {
        $stream = $this->streamFactory();

        $this->assertEquals('', $stream->read(0));
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\StreamException
     */
    public function testGetContentsException()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\StreamException::class);

                $stream = $this->streamFactory();
                $stream->detach();
        
                $stream->getContents();
    }

    /**
     *
     */
    public function testGetDetachedSize()
    {
        $stream = $this->streamFactory();
        $stream->detach();
        $this->assertEquals(null, $stream->getSize());
    }

    /**
     *
     */
    public function testGetDetachedMetaData()
    {
        $stream = $this->streamFactory();
        $stream->detach();
        $this->assertEquals(null, $stream->getMetadata('mode'));
        $this->assertEquals([], $stream->getMetadata());
    }

    /**
     *
     */
    public function testDetachedToString()
    {
        $stream = $this->streamFactory('Foo');
        $stream->detach();
        $this->assertEquals('', (string) $stream);
    }
}
