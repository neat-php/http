<?php

namespace Neat\Http\Exception;

use Throwable;

/**
 * Method not allowed exception
 *
 * A request method is not supported for the requested resource;
 * for example, a GET request on a form that requires data to be
 * presented via POST, or a PUT request on a read-only resource.
 */
class MethodNotAllowedException extends StatusException
{
    public function __construct(?string $reason = null, ?Throwable $previous = null)
    {
        parent::__construct(405, $reason, $previous);
    }
}
