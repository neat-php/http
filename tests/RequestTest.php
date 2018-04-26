<?php
namespace Neat\Http\Test;

use Neat\Http\Request;
use Neat\Http\Url;
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

    /**
     * Test with method
     */
    public function testWithMethod()
    {
        $request = new Request('GET', 'http://localhost/');

        $this->assertSame('POST', $request->withMethod('POST')->method());
    }

    /**
     * Test with URL
     */
    public function testWithUrl()
    {
        $request = new Request('GET', 'http://localhost/');
        $url     = new Url('https://example.com/');

        $this->assertSame($url, $request->withUrl($url)->url());
    }

    /**
     * Test query
     */
    public function testQuery()
    {
        $empty = new Request('GET', '/');

        $this->assertNull($empty->query('id'));
        $this->assertSame([], $empty->query());

        $request = new Request('GET', '?id=1');

        $this->assertSame('1', $request->query('id'));
        $this->assertSame(['id' => '1'], $request->query());

        $modified = $request->withQuery(['page' => 1]);

        $this->assertNull($modified->query('id'));
        $this->assertSame('1', $modified->query('page'));
        $this->assertSame(['page' => '1'], $modified->query());
        $this->assertSame('page=1', $modified->url()->query());
    }
}
