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

namespace sebastianthiel\HTTP\Tests\_support;

use sebastianthiel\HTTP\Exception\UploadedFileException;
use sebastianthiel\HTTP\UploadedFile\UploadedFile;

class UploadedFileMock extends UploadedFile
{
    protected function moveUploadedFile($sourcePath, $targetPath) : void
    {
        if (!rename($sourcePath, $targetPath)) {
            throw new UploadedFileException('Failed to move uploaded file.');
        }
        $this->moved = true;
    }
}