<?php

namespace Neat\Http\Test;

use Neat\Http\Response;
use Neat\Http\Status;
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
     * Test status code response
     */
    public function testStatusCode()
    {
        $response = new Response(404);
        $mutated  = $response->withStatus(500);

        $this->assertNotSame($response, $mutated);
        $this->assertNull($response->body());
        $this->assertSame(404, $response->status()->code());
        $this->assertSame("HTTP/1.1 404 Not Found\r\n\r\n", (string) $response);

        $this->assertSame(500, $mutated->status()->code());
        $this->assertSame("HTTP/1.1 500 Internal Server Error\r\n\r\n", (string) $mutated);
    }

    /**
     * Test status response
     */
    public function testStatus()
    {
        $response = new Response($before = new Status(404, 'Are you lost?'));
        $mutated  = $response->withStatus($after = new Status(403, 'You shall not pass!'));

        $this->assertNull($response->body());
        $this->assertSame($before, $response->status());
        $this->assertSame(404, $response->status()->code());
        $this->assertSame("HTTP/1.1 404 Are you lost?\r\n\r\n", (string) $response);

        $this->assertSame($after, $mutated->status());
        $this->assertSame(403, $mutated->status()->code());
        $this->assertSame("HTTP/1.1 403 You shall not pass!\r\n\r\n", (string) $mutated);
    }

    /**
     * Test redirect response
     */
    public function testRedirect()
    {
        $response = Response::redirect('/go-here-instead');

        $this->assertNull($response->body());
        $this->assertSame(302, $response->status()->code());
        $this->assertSame("HTTP/1.1 302 Found\r\nLocation: /go-here-instead\r\n\r\n", (string) $response);
    }
}
