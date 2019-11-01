<?php

namespace Neat\Http\Exception;

use Neat\Http\Request;
use Psr\Http\Client\RequestExceptionInterface;

class RequestException extends ClientException
{
    /** @var Request */
    private $request;

    /**
     * ClientException constructor
     *
     * @param RequestExceptionInterface $psr
     */
    public function __construct(RequestExceptionInterface $psr)
    {
        parent::__construct($psr->getMessage(), $psr->getCode(), $psr);

        $this->request = new Request($psr->getRequest());
    }
}
