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
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\UploadedFile\UploadedFileFactory;

class UploadedFileFactoryTest extends Unit
{
    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testCreateUploadedFileResource()
    {
        $factory = new UploadedFileFactory(new StreamFactory());
        $file = $factory->createUploadedFile(tmpfile(), 42, UPLOAD_ERR_PARTIAL, 'foo.txt', 'text/plain');
        $this->assertEquals(42, $file->getSize());
        $this->assertEquals(UPLOAD_ERR_PARTIAL, $file->getError());
        $this->assertEquals('foo.txt', $file->getClientFilename());
        $this->assertEquals('text/plain', $file->getClientMediaType());
    }

    /**
     *
     */
    public function testCreateUploadedFileFilePath()
    {
        $factory = new UploadedFileFactory(new StreamFactory());
        $file = $factory->createUploadedFile('12345678', null, UPLOAD_ERR_PARTIAL, 'foo.txt', 'text/plain');
        $this->assertEquals(8, $file->getSize());
        $this->assertEquals(UPLOAD_ERR_PARTIAL, $file->getError());
        $this->assertEquals('foo.txt', $file->getClientFilename());
        $this->assertEquals('text/plain', $file->getClientMediaType());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateUploadedFileInvalid()
    {
        $factory = new UploadedFileFactory(new StreamFactory());
        $factory->createUploadedFile(null, 42, UPLOAD_ERR_PARTIAL, 'foo.txt', 'text/plain');
    }
}