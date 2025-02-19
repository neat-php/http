<?php

namespace Neat\Http\Header;

use Neat\Http\Message;

interface Header
{
    public function write(Message $message): Message;

    /**
     * @return static|null
     */
    public static function read(Message $message);
}
