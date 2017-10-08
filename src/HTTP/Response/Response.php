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

namespace sebastianthiel\HTTP\Response;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use sebastianthiel\HTTP\Exception\ResponseException;
use sebastianthiel\HTTP\Message\Message;

class Response extends Message implements ResponseInterface
{
    /**  @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml */
    const MESSAGES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unassigned,',
        426 => 'Upgrade Required',
        427 => 'Unassigned,',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        430 => 'Unassigned,',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Unassigned,',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    /** @var int */
    protected $status = 200;

    /** @var string */
    protected $reasonPhrase = '';

    public function __construct(StreamFactoryInterface $streamFactory, $status = 200, $reasonPhrase = '')
    {
        parent::__construct($streamFactory);
        $this->status = $status;
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * Gets the response status code.
     *
     * @return int Status code.
     */
    public function getStatusCode() : int
    {
        return $this->status;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     *
     * @return static
     * @throws ResponseException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $code = $this->filterStatusCode($code);

        if ($reasonPhrase === '' && isset(static::MESSAGES[$code])) {
            $reasonPhrase = static::MESSAGES[$code];
        }

        $response = clone $this;
        $response->status = $code;
        $response->reasonPhrase = $reasonPhrase;

        return $response;
    }

    /**
     * validate status code
     *
     * @param $code
     *
     * @return int
     */
    protected function filterStatusCode($code) : int
    {
        if (!is_integer($code) || $code < 100 || $code > 599) {
            throw new ResponseException('Invalid HTTP status code');
        }

        return $code;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase() : string
    {
        return ($this->reasonPhrase !== '')
            ? $this->reasonPhrase
            : (isset(static::MESSAGES[$this->status])
                ? static::MESSAGES[$this->status]
                : '');
    }
}
