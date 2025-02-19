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
 * @method Header\ContentLength|null contentLength()
 * @method Message|static withContentLength(int $length)
 */
abstract class Message
{
    /** HTTP header line ending */
    public const EOL = "\r\n";

    protected MessageInterface $message;

    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

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
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (strpos($method, 'with') === 0) {
            /** @var Header\Header $header */
            $class = Header::class . '\\' . substr($method, 4);
            $header = new $class(...$arguments);

            return $header->write($this);
        }
        /** @var class-string<Header> $class */
        $class = Header::class . '\\' . ucfirst($method);

        return $class::read($this);
    }

    public function body(): string
    {
        return $this->message->getBody()->getContents();
    }

    public function bodyStream(): StreamInterface
    {
        return $this->message->getBody();
    }

    public function header(string $name): ?Header
    {
        $headerValues = $this->message->getHeader($name);
        if (count($headerValues) === 0) {
            return null;
        }

        $value = reset($headerValues);

        return new Header($name, $value);
    }

    /**
     * @return Header[]
     */
    public function headers(): array
    {
        $headers = [];
        foreach ($this->message->getHeaders() as $header => $values) {
            if (strtolower($header) !== 'set-cookie') {
                $headers[] = new Header($header, ...$values);
                continue;
            }
            foreach ($values as $value) {
                $headers[] = new Header($header, $value);
            }
        }

        return $headers;
    }

    public function version(): string
    {
        return $this->message->getProtocolVersion();
    }

    /**
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        return new static($this->message->withBody($body));
    }

    /**
     * @return static
     */
    public function withHeader(string $name, string ...$value)
    {
        return new static($this->message->withHeader($name, $value));
    }

    /**
     * @return static
     */
    public function withAddedHeader(string $name, string $value)
    {
        return new static($this->message->withAddedHeader($name, $value));
    }

    /**
     * @return static
     */
    public function withoutHeader(string $name)
    {
        return new static($this->message->withoutHeader($name));
    }

    /**
     * @return static
     */
    public function withVersion(string $version)
    {
        return new static($this->message->withProtocolVersion($version));
    }
}
