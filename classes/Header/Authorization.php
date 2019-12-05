<?php

namespace Neat\Http\Header;

use Neat\Http\Message;

/**
 * HTTP Authorization header
 *
 * @url https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization
 *
 * The HTTP Authorization request header contains the credentials to authenticate a user agent with a server, usually
 * after the server has responded with a 401 Unauthorized status and the WWW-Authenticate header.
 *
 * Syntax
 * Authorization: <type> <credentials>
 */
class Authorization implements Header
{
    const HEADER = 'Authorization';

    const TYPES = [self::TYPE_BASIC, self::TYPE_BEARER];

    const TYPE_BASIC = 'Basic';
    const TYPE_BEARER = 'Bearer';

    /** @var string */
    private $type;

    /** @var string */
    private $credentials;

    /**
     * Authorization constructor
     *
     * @param string $type
     * @param string $credentials
     */
    public function __construct(string $type, string $credentials)
    {
        $this->type        = $type;
        $this->credentials = $credentials;
    }

    /**
     * Will always return one of self::TYPES or null
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isBasic(): bool
    {
        return self::TYPE_BASIC === $this->type;
    }

    /**
     * @return bool
     */
    public function isBearer(): bool
    {
        return self::TYPE_BEARER === $this->type;
    }

    /**
     * @return string
     */
    public function credentials(): string
    {
        return $this->credentials;
    }

    /**
     * @param Message $message
     * @return Message
     */
    public function write(Message $message): Message
    {
        return $message->withHeader(self::HEADER, "{$this->type} {$this->credentials}");
    }

    /**
     * @param Message $message
     * @return self|null
     */
    public static function read(Message $message)
    {
        $header = $message->header(self::HEADER);
        if (!$header) {
            return null;
        }

        $parts = explode(' ', $header->value(), 2);
        if (2 !== count($parts)) {
            return null;
        }

        list($type, $credentials) = $parts;
        if (!in_array($type, self::TYPES)) {
            return null;
        }

        return new self($type, $credentials);
    }
}
