<?php

namespace Neat\Http\Test;

class HeaderCollector
{
    /**
     * Headers
     *
     * @var array
     */
    private static $headers = [];

    /**
     * Load the header interceptor functions
     */
    public static function start()
    {
        require_once __DIR__ . '/header.php';

        self::$headers = [];
    }

    /**
     * Add header
     *
     * @param string $header
     */
    public static function add($header)
    {
        self::$headers[] = $header;
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public static function all()
    {
        return self::$headers;
    }
}
