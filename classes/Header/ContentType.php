<?php declare(strict_types=1);

namespace Neat\Http\Header;

use Neat\Http\Message;

/**
 * Class ContentType
 *
 * @url https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
 *
 * The Content-Type entity header is used to indicate the media type of the resource.
 *
 * In responses, a Content-Type header tells the client what the content type of the returned content actually is.
 * Browsers will do MIME sniffing in some cases and will not necessarily follow the value of this header; to prevent
 * this behavior, the header X-Content-Type-Options can be set to nosniff.
 *
 * In requests, (such as POST or PUT), the client tells the server what type of data is actually sent.
 *
 * Syntax
 * Content-Type: text/html; charset=utf-8
 * Content-Type: multipart/form-data; boundary=something
 *
 * @package Neat\Http\Header
 */
class ContentType implements Header
{
    const HEADER = 'Content-Type';

    /**
     * @var string
     */
    private $value;

    /**
     * @var string|null
     */
    private $charset;

    /**
     * @var string|null
     */
    private $boundary;

    /**
     * ContentType constructor.
     * @param string      $value
     * @param string|null $charset
     * @param string|null $boundary
     */
    public function __construct(string $value, string $charset = null, string $boundary = null)
    {
        $this->value    = $value;
        $this->charset  = $charset;
        $this->boundary = $boundary;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return string|null
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * @param Message $message
     * @return Message
     */
    public function write(Message $message): Message
    {
        $header = "$this->value";
        if ($this->charset) {
            $header .= "; charset=$this->charset";
        }
        if ($this->boundary) {
            $header .= "; boundary=$this->boundary";
        }

        return $message->withHeader(self::HEADER, $header);
    }

    /**
     * @param Message $message
     * @return self|null
     */
    public static function read(Message $message)
    {
        $header = $message->header(self::HEADER);

        if (!$header) {
            return null;
        }

        $parts    = explode('; ', $header->value());
        $value    = array_shift($parts);
        $charset  = null;
        $boundary = null;
        foreach ($parts as $part) {
            if (strpos($part, 'charset') === 0) {
                list(, $charset) = explode('=', $part);
                continue;
            }
            if (strpos($part, 'boundary') === 0) {
                list(, $boundary) = explode('=', $part);
                continue;
            }
        }

        return new self($value, $charset, $boundary);
    }
}
