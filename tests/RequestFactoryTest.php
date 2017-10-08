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
use sebastianthiel\HTTP\Request\RequestFactory;
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\Uri\UriFactory;

class RequestFactoryTest extends Unit
{

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testCreateRequest()
    {
        $request = (new RequestFactory(new UriFactory(), new StreamFactory()))
            ->createRequest('PUT', 'https://example.com/');

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('example.com', $request->getUri()->getHost());
    }
}
