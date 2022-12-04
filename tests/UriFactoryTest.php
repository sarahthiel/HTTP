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
use sebastianthiel\HTTP\Uri\UriFactory;

class UriFactoryTest extends Unit
{
    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testCreateUri()
    {
        $uri = (new UriFactory())->createUri('scp://alice:secret@example.com:2222/foo/bar?baz=42#section-1');
        $this->assertEquals('scp', $uri->getScheme());
        $this->assertEquals('alice:secret', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals(2222, $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('baz=42', $uri->getQuery());
        $this->assertEquals('section-1', $uri->getFragment());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidUri()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new UriFactory())->createUri('///');
    }
}
