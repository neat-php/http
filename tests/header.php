<?php

namespace Neat\Http;

use Neat\Http\Test\HeaderCollector;

/**
 * Header intercept function (overloads the PHP built-in header function)
 *
 * @param string $string
 */
function header($string)
{
    HeaderCollector::add($string);
}
