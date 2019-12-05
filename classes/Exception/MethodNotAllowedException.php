<?php

namespace Neat\Http\Exception;

use RuntimeException;

/**
 * Method not allowed exception
 *
 * A request method is not supported for the requested resource;
 * for example, a GET request on a form that requires data to be
 * presented via POST, or a PUT request on a read-only resource.
 */
class MethodNotAllowedException extends RuntimeException
{
}
