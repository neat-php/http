<?php

namespace Neat\Http\Exception;

use Psr\Http\Client\NetworkExceptionInterface;
use RuntimeException;

class NetworkException extends RuntimeException
{
    /**
     * ClientException constructor
     *
     * @param NetworkExceptionInterface $psr
     */
    public function __construct(NetworkExceptionInterface $psr)
    {
        parent::__construct($psr->getMessage(), $psr->getCode(), $psr);
    }
}
