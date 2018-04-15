<?php
namespace Neat\Http\Test;

use Neat\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * Test empty request
     */
    public function testEmpty()
    {
        $request = new Request;

        $this->assertNull($request->body());
        $this->assertSame('', (string) $request->url());
        $this->assertsame('GET', $request->method());
    }

    /**
     * Test GET request
     */
    public function testGet()
    {
        $request = new Request('GET', 'http://localhost/');

        $this->assertNull($request->body());
        $this->assertSame('http://localhost/', (string) $request->url());
        $this->assertsame('GET', $request->method());
        $this->assertsame("GET / HTTP/1.1\r\n\r\n", (string) $request);
    }

    /**
     * Test POST request
     */
    public function testPost()
    {
        $request = new Request('POST', 'https://localhost/resource?id=1', ['json' => true]);

        $this->assertSame('{"json":true}', (string) $request->body());
        $this->assertSame('application/json', $request->header('Content-Type'));
        $this->assertSame('https://localhost/resource?id=1', (string) $request->url());
        $this->assertsame('POST', $request->method());
        $this->assertsame("POST /resource?id=1 HTTP/1.1\r\nContent-Type: application/json\r\n\r\n{\"json\":true}", (string) $request);
    }
}
