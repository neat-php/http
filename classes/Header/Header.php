<?php declare(strict_types=1);

namespace Neat\Http\Header;

use Neat\Http\Message;

interface Header
{
    /**
     * @param Message $message
     * @return Message
     */
    public function write(Message $message): Message;

    /**
     * @param Message $message
     * @return static|null
     */
    public static function read(Message $message);
}
