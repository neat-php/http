<?php

namespace Neat\Http\Exception;

use Neat\Http\Response;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseException extends ClientException
{
    /** @var Response */
    private $response;

    /**
     * ClientException constructor
     *
     * @param ResponseInterface $psr
     */
    public function __construct(ResponseInterface $psr)
    {
        parent::__construct($psr->getReasonPhrase(), $psr->getStatusCode());

        $this->response = new Response($psr);
    }
}
