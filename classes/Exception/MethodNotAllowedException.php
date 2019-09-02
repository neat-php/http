<?php declare(strict_types=1);

namespace Neat\Http\Exception;

use RuntimeException;

/**
 * Class MethodNotAllowedException
 *
 * A request method is not supported for the requested resource;
 * for example, a GET request on a form that requires data to be presented via POST, or a PUT request on a read-only resource.
 *
 * @package Neat\Http\Exception
 */
class MethodNotAllowedException extends RuntimeException
{

}