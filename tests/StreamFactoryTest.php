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

class StreamFactoryTest extends Unit
{
    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testCreateStream()
    {
        $stream = (new StreamFactory())->createStream('12345678');

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertEquals(8, $stream->getSize());
        $this->assertEquals('12345678', $stream->getContents());
    }

    /**
     *
     */
    public function testCreateStreamFromFile()
    {
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('uploadedFileTest');
        $stream = (new StreamFactory())->createStreamFromFile($filename, 'w');

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertEquals(false, $stream->isReadable());
        $this->assertEquals(true, $stream->isWritable());
        $this->assertEquals($filename, $stream->getMetadata('uri'));
    }

    /**
     *
     */
    public function testCreateStreamFromResource()
    {
        $stream = (new StreamFactory())->createStreamFromResource(tmpfile());

        $this->assertInstanceOf(StreamInterface::class, $stream);
    }
}
