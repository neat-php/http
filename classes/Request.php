<?php

namespace Neat\Http;

use Psr\Http\Message\RequestInterface;

/**
 * HTTP Request
 */
class Request extends Message
{
    /** @var RequestInterface */
    protected $message;

    /**
     * Request constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        parent::__construct($request);
    }

    /**
     * Get request as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->requestLine() . self::EOL . parent::__toString();
    }

    /**
     * @return RequestInterface
     */
    public function psr()
    {
        return $this->message;
    }

    /**
     * Get request line
     *
     * @return string
     */
    public function requestLine()
    {
        $url = $this->url();
        $uri = $url->path();
        if ($url->query()) {
            $uri .= '?' . $url->query();
        }

        return sprintf('%s %s HTTP/%s', $this->method(), $uri, $this->version());
    }

    /**
     * Get method
     *
     * @return string
     */
    public function method()
    {
        return $this->message->getMethod();
    }

    /**
     * Get URL
     *
     * @return Url
     */
    public function url()
    {
        return new Url($this->message->getUri());
    }

    /**
     * Get new request with method
     *
     * @param string $method
     * @return static
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->message = $this->message->withMethod($method);

        return $new;
    }

    /**
     * Get new request with URL
     *
     * @param Url $url
     * @return static
     */
    public function withUrl(Url $url)
    {
        $new          = clone $this;
        $new->message = $this->message->withUri($url->psr());

        return $new;
    }
}
