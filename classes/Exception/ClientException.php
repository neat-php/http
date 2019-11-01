<?php

namespace Neat\Http\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class ClientException extends RuntimeException
{
    /**
     * ClientException constructor
     *
     * @param ClientExceptionInterface $psr
     */
    public function __construct(ClientExceptionInterface $psr)
    {
        parent::__construct($psr->getMessage(), $psr->getCode(), $psr);
    }
}
