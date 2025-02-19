<?php

namespace Neat\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * URL according to RFC 3986
 *
 * @link https://tools.ietf.org/html/rfc3986
 */
class Url
{
    private UriInterface $url;

    public function __construct(UriInterface $url)
    {
        $this->url = $url;
    }

    public function __toString(): string
    {
        return $this->url->__toString();
    }

    public function psr(): UriInterface
    {
        return $this->url;
    }

    public function get(): string
    {
        return $this->url->__toString();
    }

    public function scheme(): string
    {
        return $this->url->getScheme();
    }

    public function isSecure(): bool
    {
        return $this->scheme() === 'https';
    }

    public function username(): ?string
    {
        $info = $this->url->getUserInfo();

        return $info ? explode(':', $info, 2)[0] ?? null : null;
    }

    public function password(): ?string
    {
        $info = $this->url->getUserInfo();

        return $info ? explode(':', $info, 2)[1] ?? null : null;
    }

    public function host(): string
    {
        return $this->url->getHost();
    }

    public function port(): ?int
    {
        return $this->url->getPort();
    }

    public function path(): string
    {
        return $this->url->getPath();
    }

    public function query(): string
    {
        return $this->url->getQuery();
    }

    public function fragment(): string
    {
        return $this->url->getFragment();
    }

    /**
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     */
    public function authority(): string
    {
        $authority = $this->url->getHost();
        if ($authority && $userInfo = $this->url->getUserInfo()) {
            $authority = $userInfo . '@' . $authority;
        }
        if ($authority && $port = $this->url->getPort()) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * @return static
     */
    public function withScheme(string $scheme)
    {
        $new = clone $this;
        $new->url = $this->url->withScheme($scheme);

        return $new;
    }

    /**
     * @return static
     */
    public function withUserInfo(string $username, ?string $password = null)
    {
        $new = clone $this;
        $new->url = $this->url->withUserInfo($username, $password);

        return $new;
    }

    /**
     * @return static
     */
    public function withHost(string $host)
    {
        $new = clone $this;
        $new->url = $this->url->withHost($host);

        return $new;
    }

    /**
     * @return static
     * @throws InvalidArgumentException for invalid ports
     */
    public function withPort(?int $port = null)
    {
        $new = clone $this;
        $new->url = $this->url->withPort($port);

        return $new;
    }

    /**
     * @return static
     */
    public function withPath(string $path)
    {
        $new = clone $this;
        $new->url = $this->url->withPath($path);

        return $new;
    }

    /**
     * @return static
     */
    public function withQuery(string $query)
    {
        $new = clone $this;
        $new->url = $this->url->withQuery($query);

        return $new;
    }

    /**
     * @return static
     */
    public function withQueryParameters(array $parameters)
    {
        parse_str($this->query(), $query);

        return $this->withQuery(http_build_query(array_merge($query, $parameters)));
    }

    /**
     * @return static
     */
    public function withFragment(string $fragment)
    {
        $new = clone $this;
        $new->url = $this->url->withFragment($fragment);

        return $new;
    }
}
