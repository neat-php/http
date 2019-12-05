<?php

namespace Neat\Http\Exception;

use RuntimeException;

/**
 * Route not found exception
 *
 * The requested resource could not be found but may be available in the future.
 * Subsequent requests by the client are permissible
 */
class RouteNotFoundException extends RuntimeException
{
}
