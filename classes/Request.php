<?php

namespace Neat\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP Request
 */
class Request extends Message
{
    /** @var RequestInterface */
    protected MessageInterface $message;

    public function __construct(RequestInterface $request)
    {
        parent::__construct($request);
    }

    public function __toString(): string
    {
        return $this->requestLine() . self::EOL . parent::__toString();
    }

    public function psr(): RequestInterface
    {
        return $this->message;
    }

    public function requestLine(): string
    {
        $url = $this->url();
        $uri = $url->path();
        if ($url->query()) {
            $uri .= '?' . $url->query();
        }

        return sprintf('%s %s HTTP/%s', $this->method(), $uri, $this->version());
    }

    public function method(): string
    {
        return $this->message->getMethod();
    }

    public function url(): Url
    {
        return new Url($this->message->getUri());
    }

    /**
     * @return static
     */
    public function withMethod(string $method)
    {
        $new = clone $this;
        $new->message = $this->message->withMethod($method);

        return $new;
    }

    /**
     * @return static
     */
    public function withUrl(Url $url)
    {
        $new = clone $this;
        $new->message = $this->message->withUri($url->psr());

        return $new;
    }
}
