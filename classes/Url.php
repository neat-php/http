<?php

namespace Neat\Http;

/**
 * URL according to RFC 3986
 *
 * @link https://tools.ietf.org/html/rfc3986
 */
class Url
{
    /**
     * Scheme
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * Username
     *
     * @var string
     */
    protected $username = '';

    /**
     * Password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Host
     *
     * @var string
     */
    protected $host = '';

    /**
     * Port number
     *
     * @var int
     */
    protected $port;

    /**
     * Path
     *
     * @var string
     */
    protected $path = '';

    /**
     * Query
     *
     * @var string
     */
    protected $query = '';

    /**
     * Fragment
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * Constructor
     *
     * @param string $url
     */
    public function __construct($url = null)
    {
        if ($url) {
            $this->set($url);
        }
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
    protected function get()
    {
        $url = '';
        if ($this->scheme) {
            $url = $this->scheme . ':';
        }
        if ($this->host) {
            $url .= '//' . $this->authority();
        }
        if ($this->path) {
            $url .= '/' . ltrim($this->path, '/');
        }
        if ($this->query) {
            $url .= '?' . $this->query;
        }
        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Set URL as string
     *
     * @param string $url
     */
    protected function set($url)
    {
        $parts = parse_url($url);
        if (!$parts) {
            throw new \InvalidArgumentException('URL malformed');
        }

        $this->scheme   = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $this->username = $parts['user'] ?? '';
        $this->password = $parts['pass'] ?? '';
        $this->host     = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port     = isset($parts['port']) ? intval($parts['port']) : null;
        $this->path     = $parts['path'] ?? '';
        $this->query    = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';
    }

    /**
     * Get scheme
     *
     * @return string 'http' or 'https'
     */
    public function scheme()
    {
        return $this->scheme;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Get port if non-standard
     *
     * @return int|null Port number
     */
    public function port()
    {
        if ($this->port == 80 && $this->scheme == 'http') {
            return null;
        }
        if ($this->port == 443 && $this->scheme == 'https') {
            return null;
        }

        return $this->port;
    }

    /**
     * Get path
     *
     * @return string Relative or absolute path
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Get query
     *
     * @return string Query string
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Get fragment
     *
     * @return string Fragment.
     */
    public function fragment()
    {
        return $this->fragment;
    }

    /**
     * Get authority component of the URL
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string Authority, in "[username[:password]@]host[:port]" format
     */
    public function authority()
    {
        $userInfo = $this->username;
        if ($this->password) {
            $userInfo .= ':' . $this->password;
        }
        $port      = $this->port();
        $authority = $this->host;
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
        $new = clone $this;
        $new->scheme = strtolower(rtrim($scheme, ':'));

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
        $new = clone $this;
        $new->username = $username;
        $new->password = $password;

        return $new;
    }

    /**
     * Get new URL with host
     *
     * @param string $host Host name or null to remove the host
     * @return static
     */
    public function withHost($host)
    {
        $new = clone $this;
        $new->host = strtolower($host);

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

        $new = clone $this;
        $new->port = $port;

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
        $new = clone $this;
        $new->path = $path;

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
        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * Get new URL instance with fragment
     *
     * @param string $fragment Fragment or empty string to remove the fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * Capture the URL from the SERVER super global
     *
     * @see https://secure.php.net/manual/en/reserved.variables.server.php
     * @param array $server SERVER super global
     * @return static Captured URL
     */
    public static function capture(array $server = null)
    {
        if (!$server) {
            $server = $_SERVER;
        }

        $source = 'http:';
        if (isset($server['HTTPS']) && $server['HTTPS'] && $server['HTTPS'] != 'off') {
            $source = 'https:';
        }
        if (isset($server['HTTP_HOST'])) {
            $source .= '//' . $server['HTTP_HOST'];
        }
        if (isset($server['REQUEST_URI'])) {
            $source .= $server['REQUEST_URI'];
        }

        $url = new static($source);
        $url->username = $server['PHP_AUTH_USER'] ?? null;
        $url->password = $server['PHP_AUTH_PW'] ?? null;

        return $url;
    }
}
