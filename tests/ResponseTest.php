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
use sebastianthiel\HTTP\Stream\StreamFactory;
use sebastianthiel\HTTP\Response\Response;

class ResponseTest extends Unit
{
    /**
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function responseFactory(array $options = []) : ResponseInterface
    {
        $options = array_merge(
            [
                'status' => '',
                'reason' => ''
            ],
            $options
        );

        return new Response(new StreamFactory(), $options['status'], $options['reason']);
    }

    /**************************************************************************
     *  TESTS
     *************************************************************************/

    /**
     *
     */
    public function testGetStatusCode()
    {
        $response = $this->responseFactory(['status' => 404]);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     *
     */
    public function testWithStatus()
    {
        $response = $this->responseFactory()->withStatus(404);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @expectedException \sebastianthiel\HTTP\Exception\ResponseException
     */
    public function testInvalidStatus()
    {
        $this->expectException(\sebastianthiel\HTTP\Exception\ResponseException::class);

        $this->responseFactory()->withStatus(600);
    }

    /**
     *
     */
    public function testGetReasonPhrase()
    {
        $response = $this->responseFactory(['status' => 404]);

        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }

    /**
     *
     */
    public function testCustomReasonPhrase()
    {
        $response = $this->responseFactory(['status' => 404, 'reason' => 'Foo']);

        $this->assertEquals('Foo', $response->getReasonPhrase());
    }
}
