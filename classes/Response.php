<?php

namespace Neat\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Response
 */
class Response extends Message
{
    /** @var ResponseInterface */
    protected MessageInterface $message;

    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
    }

    public function __toString(): string
    {
        return $this->statusLine() . self::EOL . parent::__toString();
    }

    public function psr(): ResponseInterface
    {
        return $this->message;
    }

    public function statusLine(): string
    {
        return "HTTP/{$this->version()} {$this->status()}";
    }

    public function status(): Status
    {
        if ($this->message->getStatusCode()) {
            return new Status($this->message->getStatusCode(), $this->message->getReasonPhrase());
        }
        $body = $this->message->getBody();

        return new Status($body->getSize() === 0 ? 204 : 200);
    }

    /**
     * @param int|Status $status
     */
    protected function setStatus($status): void
    {
        if ($status instanceof Status) {
            $this->message = $this->message->withStatus($status->code(), $status->reason());
        } else {
            $this->message = $this->message->withStatus($status);
        }
    }

    /**
     * @param int|Status $status
     * @return static
     */
    public function withStatus($status)
    {
        $new = clone $this;
        $new->setStatus($status);

        return $new;
    }
}
