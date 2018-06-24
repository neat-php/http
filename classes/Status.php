<?php

namespace Neat\Http;

/**
 * Status according to RFC 7231
 *
 * @link https://tools.ietf.org/html/rfc7231#section-6
 */
class Status
{
    /**
     * @var string[]
     */
    const REASONS = [
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
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $reason;

    /**
     * Status constructor
     *
     * @param int    $code
     * @param string $reason (optional)
     */
    public function __construct(int $code, string $reason = null)
    {
        $this->code   = $code;
        $this->reason = $reason ?? self::REASONS[$code] ?? 'Unknown';
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%d %s', $this->code, $this->reason);
    }

    /**
     * Get code
     *
     * @return int
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function reason()
    {
        return $this->reason;
    }
}
