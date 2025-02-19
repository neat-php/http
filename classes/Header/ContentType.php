<?php

namespace Neat\Http\Header;

use Neat\Http\Message;

/**
 * HTTP ContentType header
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
 */
class ContentType implements Header
{
    public const HEADER = 'Content-Type';

    private string $type;
    private ?string $charset;
    private ?string $boundary;

    public function __construct(string $type, ?string $charset = null, ?string $boundary = null)
    {
        $this->type = $type;
        $this->charset = $charset;
        $this->boundary = $boundary;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function charset(): ?string
    {
        return $this->charset;
    }

    public function boundary(): ?string
    {
        return $this->boundary;
    }

    public function write(Message $message): Message
    {
        $header = "$this->type";
        if ($this->charset) {
            $header .= "; charset=$this->charset";
        }
        if ($this->boundary) {
            $header .= "; boundary=$this->boundary";
        }

        return $message->withHeader(self::HEADER, $header);
    }

    public static function read(Message $message): ?self
    {
        $header = $message->header(self::HEADER);

        if (!$header) {
            return null;
        }

        $parts = explode('; ', $header->value());
        $value = array_shift($parts);
        $charset = null;
        $boundary = null;
        foreach ($parts as $part) {
            if (strpos($part, 'charset') === 0) {
                [, $charset] = explode('=', $part);
                continue;
            }
            if (strpos($part, 'boundary') === 0) {
                [, $boundary] = explode('=', $part);
                continue;
            }
        }

        return new self($value, $charset, $boundary);
    }
}
