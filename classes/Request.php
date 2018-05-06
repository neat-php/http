<?php
namespace Neat\Http;

/**
 * HTTP Request
 */
class Request extends Message
{
    /**
     * Method in uppercase
     *
     * @var string
     */
    protected $method;

    /**
     * URL
     *
     * @var Url
     */
    protected $url;

    /**
     * Query (aka GET) parameters
     *
     * @var array
     */
    protected $query = [];

    /**
     * Parsed body (aka POST) parameters
     *
     * @var array
     */
    protected $post = [];

    /**
     * Cookie parameters
     *
     * @var array
     */
    protected $cookie = [];

    /**
     * Request constructor.
     *
     * @param string     $method
     * @param string|Url $url
     * @param string     $body
     */
    public function __construct($method = 'GET', $url = null, $body = null)
    {
        $this->setMethod($method);
        $this->setUrl($url);
        $this->setBody($body);
    }

    /**
     * Get request as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->requestLine() . self::EOL . parent::__toString();
    }

    /**
     * Get request line
     *
     * @return string
     */
    public function requestLine()
    {
        $uri = $this->url->path();
        if ($this->url->query()) {
            $uri .= '?' . $this->url->query();
        }

        return sprintf('%s %s HTTP/%s', $this->method, $uri, $this->version);
    }

    /**
     * Get method
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Get URL
     *
     * @return Url
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * Get query (aka GET) parameter(s)
     *
     * @param string $var
     * @return mixed
     */
    public function query($var = null)
    {
        if ($var === null) {
            return $this->query;
        }

        return $this->query[$var] ?? null;
    }

    /**
     * Get parsed body (aka POST) parameter(s)
     *
     * @param string $var
     * @return mixed
     */
    public function post($var = null)
    {
        if ($var === null) {
            return $this->post;
        }

        return $this->post[$var] ?? null;
    }

    /**
     * Get cookie parameter(s)
     *
     * @param string $name
     * @return mixed
     */
    public function cookie($name = null)
    {
        if ($name === null) {
            return $this->cookie;
        }

        return $this->cookie[$name] ?? null;
    }

    /**
     * Set method
     *
     * @param string $method
     */
    protected function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Set URL
     *
     * @param string $url
     */
    protected function setUrl($url)
    {
        $this->url = $url instanceof Url ? $url : new Url($url);

        parse_str($this->url->query(), $this->query);
    }

    /**
     * Set body
     *
     * @param string $body
     */
    protected function setBody($body = null)
    {
        parent::setBody($body);

        if (is_array($body) || is_object($body)) {
            $this->post = (array) $body;
        } else {
            $this->post = [];
        }
    }

    /**
     * Set cookie parameters
     *
     * @param string $name
     * @param string $value
     */
    protected function setCookie($name, $value = null)
    {
        if ($value !== null) {
            $this->cookie[$name] = $value;
        } elseif (isset($this->cookie[$name])) {
            unset($this->cookie[$name]);
        }
    }

    /**
     * Get new request with method
     *
     * @param string $method
     * @return Request
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->setMethod($method);

        return $new;
    }

    /**
     * Get new request with URL
     *
     * @param string|Url $url
     * @return Request
     */
    public function withUrl($url)
    {
        $new = clone $this;
        $new->setUrl($url);

        return $new;
    }

    /**
     * Get new request with query parameters
     *
     * @param array $query
     * @return Request
     */
    public function withQuery(array $query)
    {
        $new = clone $this;
        $new->setUrl($this->url->withQuery(http_build_query($query)));

        return $new;
    }

    /**
     * Get new request with cookie parameter
     *
     * @param string $name
     * @param string $value
     * @return Request
     */
    public function withCookie($name, $value)
    {
        $new = clone $this;
        $new->setCookie($name, $value);

        return $new;
    }
}
