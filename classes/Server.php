<?php

namespace Neat\Http;

class Server
{
    /** @var callable */
    private $requestFactory;

    /**
     * Server constructor.
     *
     * @param callable $requestFactory
     */
    public function __construct(callable $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Receive request
     *
     * @return Request
     */
    public function receive(): Request
    {
        return ($this->requestFactory)();
    }

    /**
     * Send response
     *
     * @param Response|mixed $response
     */
    public function send($response)
    {
        header($response->statusLine());
        foreach ($response->headers() as $header) {
            header($header->line());
        }

        $body = $response->psr()->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        if (!$body->isReadable()) {
            echo $body;

            return;
        }

        while (!$body->eof()) {
            echo $body->read(1024);
        }
    }
}
