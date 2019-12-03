<?php declare(strict_types=1);

namespace Neat\Http\Exception;

use Neat\Http\Status;
use RuntimeException;
use Throwable;

class StatusException extends RuntimeException
{
    /** @var Status */
    protected $status;

    /**
     * Exception constructor
     *
     * @param int       $code
     * @param string    $reason   (optional)
     * @param Throwable $previous (optional)
     */
    public function __construct(int $code, string $reason = null, Throwable $previous = null)
    {
        $this->status = new Status($code, $reason);

        parent::__construct($this->status->reason(), $this->status->code(), $previous);
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function status()
    {
        return $this->status;
    }
}
