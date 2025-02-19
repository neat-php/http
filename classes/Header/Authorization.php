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
    public const HEADER = 'Authorization';

    public const TYPES = [self::TYPE_BASIC, self::TYPE_BEARER];

    public const TYPE_BASIC = 'Basic';
    public const TYPE_BEARER = 'Bearer';

    private string $type;
    private string $credentials;

    public function __construct(string $type, string $credentials)
    {
        $this->type = $type;
        $this->credentials = $credentials;
    }

    /**
     * @return self::TYPE_*
     */
    public function type(): string
    {
        return $this->type;
    }

    public function isBasic(): bool
    {
        return self::TYPE_BASIC === $this->type;
    }

    public function isBearer(): bool
    {
        return self::TYPE_BEARER === $this->type;
    }

    public function credentials(): string
    {
        return $this->credentials;
    }

    public function write(Message $message): Message
    {
        return $message->withHeader(self::HEADER, "$this->type $this->credentials");
    }

    public static function read(Message $message): ?self
    {
        $header = $message->header(self::HEADER);
        if (!$header) {
            return null;
        }

        $parts = explode(' ', $header->value(), 2);
        if (2 !== count($parts)) {
            return null;
        }

        [$type, $credentials] = $parts;
        if (!in_array($type, self::TYPES)) {
            return null;
        }

        return new self($type, $credentials);
    }
}
