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
        if (is_int($response)) {
            $this->status = new Status($response);
        } else {
            $this->setBody($response);
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
