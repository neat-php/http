<?php

namespace Neat\Http\Header;

use Neat\Http\Message;

class ContentLength implements Header
{
    const HEADER = 'Content-Length';

    /** @var int */
    private $length;

    /**
     * ContentLength constructor.
     *
     * @param int $length
     */
    public function __construct(int $length)
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return $this->length;
    }

    /**
     * @inheritDoc
     */
    public function write(Message $message): Message
    {
        return $message->withHeader(self::HEADER, $this->length);
    }

    /**
     * @inheritDoc
     */
    public static function read(Message $message)
    {
        $header = $message->header(self::HEADER);
        if (!$header) {
            return null;
        }

        return new self($header->value());
    }
}
