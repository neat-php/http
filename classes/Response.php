<?php

namespace Neat\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Response
 */
class Response extends Message
{
    /** @var ResponseInterface */
    protected $message;

    /**
     * Response constructor
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
    }

    /**
     * Get response as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->statusLine() . self::EOL . parent::__toString();
    }

    /**
     * @return ResponseInterface
     */
    public function psr(): ResponseInterface
    {
        return $this->message;
    }

    /**
     * Get status line
     *
     * @return string
     */
    public function statusLine(): string
    {
        return 'HTTP/' . $this->version() . ' ' . $this->status();
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function status(): Status
    {
        if ($this->message->getStatusCode()) {
            return new Status($this->message->getStatusCode(), $this->message->getReasonPhrase());
        }
        $body = $this->message->getBody();

        return new Status($body->getSize() === 0 ? 204 : 200);
    }

    /**
     * Status
     *
     * @param int|Status $status
     */
    protected function setStatus($status)
    {
        if ($status instanceof Status) {
            $this->message = $this->message->withStatus($status->code(), $status->reason());
        } else {
            $this->message = $this->message->withStatus($status);
        }
    }

    /**
     * Get response with status
     *
     * @param int|Status $status
     * @return Response
     */
    public function withStatus($status)
    {
        $new = clone $this;
        $new->setStatus($status);

        return $new;
    }
}
