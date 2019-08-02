<?php declare(strict_types=1);

namespace Neat\Http;

use Psr\Http\Message\UriInterface;

/**
 * URL according to RFC 3986
 *
 * @link https://tools.ietf.org/html/rfc3986
 */
class Url
{
    /**
     * @var UriInterface
     */
    private $url;

    /**
     * Constructor
     *
     * @param UriInterface $url
     */
    public function __construct(UriInterface $url)
    {
        $this->url = $url;
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->url;
    }

    /**
     * Get URL as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Get URL as string
     *
     * @return string
     */
    public function get()
    {
        $url = '';
        if ($this->scheme()) {
            $url = $this->scheme() . ':';
        }
        if ($this->host()) {
            $url .= '//' . $this->authority();
        }
        if ($this->path()) {
            $url .= '/' . ltrim($this->path(), '/');
        }
        if ($this->query()) {
            $url .= '?' . $this->query();
        }
        if ($this->fragment()) {
            $url .= '#' . $this->fragment();
        }

        return $url;
    }

    /**
     * Get scheme
     *
     * @return string 'http' or 'https'
     */
    public function scheme()
    {
        return $this->url->getScheme();
    }

    /**
     * Returns whether https is used or not
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->scheme() === 'https';
    }

    /**
     * @return array
     */
    protected function userInfo(): array
    {
        $userInfo      = $this->url->getUserInfo();
        $userInfoParts = $userInfo ? explode(':', $userInfo, 2) : [];
        if (count($userInfoParts) === 0) {
            return [
                'name'     => null,
                'password' => null,
            ];
        }
        if (count($userInfoParts) === 1) {
            return [
                'name'     => reset($userInfoParts),
                'password' => null,
            ];
        }
        list($name, $password) = $userInfoParts;

        return [
            'name'     => $name,
            'password' => $password,
        ];
    }

    /**
     * Get username
     *
     * @return string
     */
    public function username()
    {
        return $this->userInfo()['name'];
    }

    /**
     * Get password
     *
     * @return string
     */
    public function password()
    {
        return $this->userInfo()['password'];
    }

    /**
     * Get host
     *
     * @return string
     */
    public function host()
    {
        return $this->url->getHost();
    }

    /**
     * Get port if non-standard
     *
     * @return int|null Port number
     */
    public function port()
    {
        if ($this->url->getPort() == 80 && $this->scheme() == 'http') {
            return null;
        }
        if ($this->url->getPort() == 443 && $this->scheme() == 'https') {
            return null;
        }

        return $this->url->getPort();
    }

    /**
     * Get path
     *
     * @return string Relative or absolute path
     */
    public function path()
    {
        return $this->url->getPath();
    }

    /**
     * Get query
     *
     * @return string Query string
     */
    public function query()
    {
        return $this->url->getQuery();
    }

    /**
     * Get fragment
     *
     * @return string Fragment.
     */
    public function fragment()
    {
        return $this->url->getFragment();
    }

    /**
     * Get authority component of the URL
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string Authority, in "[username[:password]@]host[:port]" format
     */
    public function authority()
    {
        $userInfo = $this->username();
        if ($this->password()) {
            $userInfo .= ':' . $this->password();
        }
        $port      = $this->port();
        $authority = $this->host();
        if ($authority && $userInfo) {
            $authority = $userInfo . '@' . $authority;
        }
        if ($authority && $port) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * Get new URL with scheme
     *
     * @param string $scheme Scheme to use or null to remove the scheme
     * @return static
     */
    public function withScheme($scheme)
    {
        $new      = clone $this;
        $new->url = $this->url->withScheme($scheme);

        return $new;
    }

    /**
     * Get new URL with username (and optionally a password)
     *
     * @param string $username Username to use or null to remove the username
     * @param string $password Associated password
     * @return static
     */
    public function withUserInfo($username, $password = '')
    {
        $new      = clone $this;
        $new->url = $this->url->withUserInfo($username, $password);

        return $new;
    }

    /**
     * Get new URL with host
     *
     * @param string $host Host name or null to remove the host
     * @return static
     */
    public function withHost(string $host)
    {
        $new      = clone $this;
        $new->url = $this->url->withHost($host);

        return $new;
    }

    /**
     * Get new URL with port
     *
     * @param int|null $port Port number to add or null to remove the port number
     * @return static
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort(int $port = null)
    {
        if ($port !== null && ($port < 0 || $port > 65536)) {
            throw new \InvalidArgumentException('Invalid port number: ' . $port);
        }

        $new      = clone $this;
        $new->url = $this->url->withPort($port);

        return $new;
    }

    /**
     * Get new URL instance with path
     *
     * @param string $path Path or empty string to remove the path
     * @return static
     */
    public function withPath($path)
    {
        $new      = clone $this;
        $new->url = $this->url->withPath($path);

        return $new;
    }

    /**
     * Get new URL instance with query
     *
     * @param string $query Query string or empty string to remove the query
     * @return static
     */
    public function withQuery($query)
    {
        $new      = clone $this;
        $new->url = $this->url->withQuery($query);

        return $new;
    }

    /**
     * @param array $parameters
     * @return static
     */
    public function withQueryParameters(array $parameters)
    {
        parse_str($this->query(), $query);

        return $this->withQuery(build_query(array_merge($query, $parameters)));
    }

    /**
     * Get new URL instance with fragment
     *
     * @param string $fragment Fragment or empty string to remove the fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        $new      = clone $this;
        $new->url = $this->url->withFragment($fragment);

        return $new;
    }
}
