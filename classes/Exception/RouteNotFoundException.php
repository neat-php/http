<?php declare(strict_types=1);

namespace Neat\Http\Exception;

use RuntimeException;

/**
 * Class RouteNotFoundException
 *
 * The requested resource could not be found but may be available in the future.
 * Subsequent requests by the client are permissible
 *
 * @package Neat\Http\Exception
 */
class RouteNotFoundException extends RuntimeException
{

}