<?php

namespace Neat\Http\Header;

use Neat\Http\Message;

class ContentLength implements Header
{
    public const HEADER = 'Content-Length';

    private int $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function length(): int
    {
        return $this->length;
    }

    public function write(Message $message): Message
    {
        return $message->withHeader(self::HEADER, $this->length);
    }

    public static function read(Message $message): ?self
    {
        $header = $message->header(self::HEADER);
        if (!$header) {
            return null;
        }

        return new self($header->value());
    }
}
