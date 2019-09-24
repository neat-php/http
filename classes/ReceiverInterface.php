<?php

namespace Neat\Http;

/**
 * @deprecated Use the Receiver instead
 */
interface ReceiverInterface
{
    /**
     * @return Request
     */
    public function request(): Request;
}
