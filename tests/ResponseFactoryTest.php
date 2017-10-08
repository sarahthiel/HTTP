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
use Psr\Http\Message\ResponseInterface;
use sebastianthiel\HTTP\Response\ResponseFactory;
use sebastianthiel\HTTP\Stream\StreamFactory;

class ResponseFactoryTest extends Unit
{
    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testCreateResponse()
    {
        $response = (new ResponseFactory(new StreamFactory()))->createResponse(301);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(301, $response->getStatusCode());
    }
}
