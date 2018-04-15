<?php
namespace Neat\Http\Test;

use Neat\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * Test empty response
     */
    public function testEmpty()
    {
        $response = new Response;

        $this->assertNull($response->body());
        $this->assertSame(204, $response->status()->code());
        $this->assertSame("HTTP/1.1 204 No Content\r\n\r\n", (string) $response);
    }

    /**
     * Test string response
     */
    public function testString()
    {
        $response = new Response('Hello world!');

        $this->assertSame('Hello world!', $response->body());
        $this->assertSame(200, $response->status()->code());
        $this->assertSame("HTTP/1.1 200 OK\r\n\r\nHello world!", (string) $response);
    }

    /**
     * Test JSON response
     */
    public function testJson()
    {
        $response = new Response(['json' => true]);

        $this->assertSame('{"json":true}', $response->body());
        $this->assertSame('application/json', $response->header('Content-Type'));
        $this->assertSame(200, $response->status()->code());
        $this->assertSame("HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n{\"json\":true}", (string) $response);
    }

    /**
     * Test status response
     */
    public function testStatus()
    {
        $response = new Response(404);

        $this->assertNull($response->body());
        $this->assertSame(404, $response->status()->code());
        $this->assertSame("HTTP/1.1 404 Not Found\r\n\r\n", (string) $response);
    }
}
