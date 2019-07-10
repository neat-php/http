<?php declare(strict_types=1);

namespace Neat\Http;

use Neat\Http\Header\Authorization;
use Neat\Http\Header\ContentType;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP Message
 */
abstract class Message
{
    /**
     * HTTP header line ending
     */
    const EOL = "\r\n";

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * Message constructor.
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
     * @return Authorization|null
     */
    public function authorization()
    {
        return Authorization::read($this);
    }

    /**
     * @param $type
     * @param $credentials
     * @return static
     */
    public function withAuthorization(string $type, string $credentials)
    {
        $authorization = new Authorization($type, $credentials);

        return $authorization->write($this);
    }

    /**
     * @return ContentType|null
     */
    public function contentType()
    {
        return ContentType::read($this);
    }

    /**
     * @param string      $value
     * @param string|null $charset
     * @param string|null $boundary
     * @return static
     */
    public function withContentType(string $value, string $charset = null, string $boundary = null)
    {
        $contentType = new ContentType($value, $charset, $boundary);

        return $contentType->write($this);
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
     * Set body
     *
     * @param StreamInterface $stream
     */
    protected function setBody(StreamInterface $stream)
    {
        $this->message = $this->message->withBody($stream);
    }

    /**
     * Get instance with body
     *
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->setBody($body);

        return $new;
    }

    /**
     * Set header
     *
     * @param string $name
     * @param string $value
     */
    protected function setHeader($name, $value)
    {
        $this->message = $this->message->withHeader($name, $value);
    }

    /**
     * Get instance with header
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->setHeader($name, $value);

        return $new;
    }

    /**
     * Get instance without header
     *
     * @param string $name
     * @return static
     */
    public function withoutHeader($name)
    {
        $new          = clone $this;
        $new->message = $this->message->withoutHeader($name);

        return $new;
    }

    /**
     * Get instance with version
     *
     * @param string $version
     * @return static
     */
    public function withVersion(string $version)
    {
        $new          = clone $this;
        $new->message = $this->message->withProtocolVersion($version);

        return $new;
    }
}
