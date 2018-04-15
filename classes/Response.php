<?php
namespace Neat\Http;

/**
 * HTTP Response
 */
class Response extends Message
{
    /**
     * Status
     *
     * @var Status
     */
    protected $status;

    /**
     * Response constructor
     *
     * @param mixed $response
     */
    public function __construct($response = null)
    {
        if (is_string($response)) {
            $this->body = $response;
        }
        if (is_int($response)) {
            $this->status = new Status($response);
        }
        if (is_array($response) || is_object($response)) {
            $this->body = json_encode($response);
            $this->headers['content-type'] = new Header('Content-Type', 'application/json');
        }
    }

    /**
     * Get response as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->statusLine() . self::EOL . parent::__toString();
    }

    /**
     * Get status line
     *
     * @return string
     */
    public function statusLine()
    {
        return 'HTTP/' . $this->version . ' ' . $this->status();
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function status()
    {
        if ($this->status) {
            return $this->status;
        }

        return new Status($this->body === null ? 204 : 200);
    }
}
