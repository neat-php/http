<?php

namespace Neat\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP Message
 *
 * @method Header\Authorization|null authorization()
 * @method Message|static withAuthorization(string $type, string $credentials)
 * @method Header\ContentDisposition|null contentDisposition()
 * @method Message|static withContentDisposition(string $disposition, string $filename = null, string $name = null)
 * @method Header\ContentType|null contentType()
 * @method Message|static withContentType(string $type, string $charset = null, string $boundary = null)
 */
abstract class Message
{
    /** HTTP header line ending */
    const EOL = "\r\n";

    /** @var MessageInterface */
    protected $message;

    /**
     * Message constructor
     *
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * Get message as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        $message = '';
        foreach ($this->headers() as $header) {
            $message .= $header->line() . self::EOL;
        }
        $message .= self::EOL . $this->message->getBody()->getContents();

        return $message;
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (strpos($method, 'with') === 0) {
            /** @var Header\Header $header */
            $class  = Header::class . '\\' . substr($method, 4);
            $header = new $class(...$arguments);

            return $header->write($this);
        }

        $class = Header::class . '\\' . ucfirst($method);

        /** @noinspection PhpUndefinedMethodInspection */
        return $class::read($this);
    }

    /**
     * Get body
     *
     * @return string
     */
    public function body(): string
    {
        return $this->message->getBody()->getContents();
    }

    /**
     * @return StreamInterface
     */
    public function bodyStream(): StreamInterface
    {
        return $this->message->getBody();
    }

    /**
     * Get header value by name
     *
     * @param string $name
     * @return Header|null
     */
    public function header(string $name)
    {
        $headerValues = $this->message->getHeader($name);
        if (count($headerValues) === 0) {
            return null;
        }

        $value = reset($headerValues);

        return new Header($name, $value);
    }

    /**
     * Get header values
     *
     * @return Header[]
     */
    public function headers()
    {
        $headers = $this->message->getHeaders();

        return array_map(function (string $name, array $values) {
            return new Header($name, reset($values));
        }, array_keys($headers), $headers);
    }

    /**
     * Get HTTP version
     *
     * @return string
     */
    public function version()
    {
        return $this->message->getProtocolVersion();
    }

    /**
     * Get instance with body
     *
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        return new static($this->message->withBody($body));
    }

    /**
     * Get instance with header
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public function withHeader(string $name, string $value)
    {
        return new static($this->message->withHeader($name, $value));
    }

    /**
     * Get instance without header
     *
     * @param string $name
     * @return static
     */
    public function withoutHeader(string $name)
    {
        return new static($this->message->withoutHeader($name));
    }

    /**
     * Get instance with version
     *
     * @param string $version
     * @return static
     */
    public function withVersion(string $version)
    {
        return new static($this->message->withProtocolVersion($version));
    }
}
