<?php

namespace Neat\Http;

interface Receiver extends ReceiverInterface
{
    /**
     * @return Request
     */
    public function request(): Request;
}
