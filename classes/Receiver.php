<?php

namespace Neat\Http;

interface Receiver
{
    /**
     * @return Request
     */
    public function request(): Request;
}
