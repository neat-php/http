<?php declare(strict_types=1);

namespace Neat\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * HTTP Request
 */
class Request extends Message
{
    /**
     * @var ServerRequestInterface
     */
    protected $message;

    /**
     * Request constructor
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
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
     * @return ServerRequestInterface
     */
    public function psr(): ServerRequestInterface
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
     * Get query (aka GET) parameter(s)
     *
     * @param string $var
     * @return mixed
     */
    public function query($var = null)
    {
        if ($var === null) {
            return $this->message->getQueryParams();
        }

        return $this->message->getQueryParams()[$var] ?? null;
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
            return $this->message->getParsedBody();
        }

        return $this->message->getParsedBody()[$var] ?? null;
    }

    /**
     * Get multipart files
     *
     * @param array $key
     * @return null|Upload|Upload[]|Upload[][]|...
     */
    public function files(...$key)
    {
        $files = $this->toFiles($this->message->getUploadedFiles());
        while ($key) {
            $files = $files[array_shift($key)] ?? null;
        }

        return $files;
    }

    /**
     * @param array $files
     * @return Upload|Upload[]|Upload[][]
     */
    protected function toFiles(array $files): array
    {
        return array_map(function ($file) {
            if (is_array($file)) {
                return $this->toFiles($file);
            }

            return new Upload($file);
        }, $files);
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
            return $this->message->getCookieParams();
        }

        return $this->message->getCookieParams()[$name] ?? null;
    }

    /**
     * @param string $var
     * @return string|null
     */
    public function server(string $var)
    {
        return $this->message->getServerParams()[$var] ?? null;
    }

    /**
     * @return string|null
     */
    public function clientIp()
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * Set method
     *
     * @param string $method
     */
    protected function setMethod($method)
    {
        $this->message = $this->message->withMethod($method);
    }

    /**
     * Set URL
     *
     * @param Url $url
     */
    protected function setUrl(Url $url)
    {
        $this->message = $this->message->withUri($url->getUri());
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
            $this->message = $this->message->withCookieParams(array_merge($this->cookie(), [$name => $value]));
        } elseif ($this->cookie($name)) {
            $cookies = $this->cookie();
            unset($cookies[$name]);
            $this->message = $this->message->withCookieParams($cookies);
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
     * @param Url $url
     * @return Request
     */
    public function withUrl(Url $url)
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
        $new->setUrl($this->url()->withQuery(http_build_query($query)));

        return $new;
    }

    /**
     * Get new request with uploaded files
     *
     * @param Upload[] $files
     * @return Request
     */
    public function withFiles(array $files)
    {
        $new          = clone $this;
        $new->message = $this->message->withUploadedFiles(array_map(function (Upload $upload): UploadedFileInterface {
            return $upload->file();
        }, $files));

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
