<?php

namespace Neat\Http\Exception;

/**
 * Route not found exception
 *
 * The requested resource could not be found but may be available in the future.
 * Subsequent requests by the client are permissible
 */
class RouteNotFoundException extends StatusException
{
    public function __construct(string $reason = null, \Throwable $previous = null)
    {
        parent::__construct(404, $reason, $previous);
    }
}
