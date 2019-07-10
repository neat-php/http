<?php

namespace Neat\Http\Test;

use Neat\Http\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testIn()
    {
        $router = new Router();
        $group  = $router->in('/test');
        $this->assertNotSame($router, $group);
    }
}
