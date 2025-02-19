<?php

namespace Neat\Http\Exception;

use Neat\Http\Status;
use RuntimeException;
use Throwable;

class StatusException extends RuntimeException
{
    protected Status $status;

    public function __construct(int $code, ?string $reason = null, ?Throwable $previous = null)
    {
        $this->status = new Status($code, $reason);

        parent::__construct($this->status->reason(), $this->status->code(), $previous);
    }

    public function status(): Status
    {
        return $this->status;
    }
}
