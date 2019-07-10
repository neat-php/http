<?php

namespace Neat\Http;

interface ReceiverInterface
{
    /**
     * @return Request
     */
    public function request(): Request;
}
