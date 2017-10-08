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
use Psr\Http\Message\UploadedFileInterface;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\Tests\_support\UploadedFileMock;

class UploadedFileTest extends Unit
{
    /**
     * @param string $content
     *
     * @return StreamInterface
     */
    public function tmpFileStreamFactory($content = '') : StreamInterface
    {
        $stream = (new StreamFactory())->createStreamFromResource(tmpfile());
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }

    /**
     * @param string $content
     *
     * @return StreamInterface
     */
    public function streamFactory($content = '') : StreamInterface
    {
        $stream = (new StreamFactory())->createStream($content);

        return $stream;
    }

    /**
     * @param array $options
     *
     * @return UploadedFileInterface
     */
    public function uploadedFileFactory($options = []) : UploadedFileInterface
    {
        $options = array_merge(
            [
                'stream'       => null,
                'file_content' => '',
                'error'        => UPLOAD_ERR_OK,
                'size'         => null,
                'filename'     => null,
                'mediatype'    => null,
            ],
            $options
        );

        if ($options['stream'] === null) {
            $options['stream'] = $this->tmpFileStreamFactory($options['file_content']);
        }

        return new UploadedFileMock($options['stream'], $options['error'], $options['size'], $options['filename'], $options['mediatype']);
    }

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testGetStream()
    {
        $file = $this->uploadedFileFactory(['file_content' => '12345678']);
        $this->assertEquals('12345678', $file->getStream()->getContents());
    }

    /**
     *
     */
    public function testGetSize()
    {
        $file = $this->uploadedFileFactory(['size' => 8]);
        $this->assertEquals(8, $file->getSize());
    }

    /**
     *
     */
    public function testGetError()
    {
        $file = $this->uploadedFileFactory(['error' => UPLOAD_ERR_PARTIAL]);
        $this->assertEquals(UPLOAD_ERR_PARTIAL, $file->getError());
    }

    /**
     *
     */
    public function testGetClientFilename()
    {
        $file = $this->uploadedFileFactory(['filename' => 'foo.txt']);
        $this->assertEquals('foo.txt', $file->getClientFilename());
    }

    /**
     *
     */
    public function testGetClientMediaType()
    {
        $file = $this->uploadedFileFactory(['mediatype' => 'text/plain']);
        $this->assertEquals('text/plain', $file->getClientMediaType());
    }

    /**
     *
     */
    public function testMoveFileTo()
    {
        $file = $this->uploadedFileFactory(['file_content' => '12345678']);

        $targetName = uniqid('uploadedFileTest');
        $targetPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $targetName;

        $file->moveTo($targetPath);

        $this->assertFileExists($targetPath);
        $this->assertEquals('12345678', file_get_contents($targetPath));

        unlink($targetPath);
    }

    /**
     *
     */
    public function testWriteStreamTo()
    {
        $stream = $this->streamFactory('12345678');
        $file = $this->uploadedFileFactory(['stream' => $stream]);

        $targetName = uniqid('uploadedFileTest');
        $targetPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $targetName;

        $file->moveTo($targetPath);

        $this->assertFileExists($targetPath);
        $this->assertEquals('12345678', file_get_contents($targetPath));

        unlink($targetPath);
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UploadedFileException
     */
    public function testMovedStream()
    {
        $file = $this->uploadedFileFactory(['file_content' => '']);

        $targetName = uniqid('uploadedFileTest');
        $targetPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $targetName;

        $file->moveTo($targetPath);
        unlink($targetPath);

        $file->getStream();
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\UploadedFileException
     */
    public function testMoveErroredFileTo()
    {
        $file = $this->uploadedFileFactory(['file_content' => '12345678', 'error' => UPLOAD_ERR_CANT_WRITE]);

        $targetName = uniqid('uploadedFileTest');
        $targetPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $targetName;

        $file->moveTo($targetPath);

        $this->assertFileExists($targetPath);
        $this->assertEquals('12345678', file_get_contents($targetPath));

        unlink($targetPath);
    }
}
