<?php

namespace Neat\Http;

/**
 * Transmitter interface
 *
 * @deprecated Use Neat\Http\Server\Server instead.
 */
interface Receiver
{
    /**
     * @return Request
     */
    public function request(): Request;
}
