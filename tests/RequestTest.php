<?php

namespace Neat\Http\Test;

use Neat\Http\Request;
use Neat\Http\Upload;
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
        $this->assertSame([], $request->query());
        $this->assertSame([], $request->post());
        $this->assertSame([], $request->files());
        $this->assertSame([], $request->cookie());
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
        $this->assertSame(['json' => true], $request->post());
        $this->assertSame(true, $request->post('json'));
        $this->assertNull($request->post('unknown'));
        $this->assertSame('application/json', $request->header('Content-Type'));
        $this->assertSame('https://localhost/resource?id=1', (string) $request->url());
        $this->assertsame('POST', $request->method());
        $this->assertsame("POST /resource?id=1 HTTP/1.1\r\nContent-Type: application/json\r\n\r\n{\"json\":true}", (string) $request);
    }

    /**
     * Test with files
     */
    public function testWithFiles()
    {
        $request = new Request('POST');
        $mutated = $request->withFiles(['avatar' => [
                'tmp_name' => __DIR__ . '/test.txt',
                'name'     => 'my-avatar.png',
                'size'     => 90996,
                'type'     => 'image/png',
                'error'    => 0,
            ],
        ]);

        $this->assertInstanceOf(Upload::class, $mutated->files('avatar'));
        $this->assertEquals(['avatar' => new Upload(__DIR__ . '/test.txt', 'my-avatar.png', 'image/png')], $mutated->files());
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

    /**
     * Test cookie
     */
    public function testCookie()
    {
        $request  = new Request;
        $modified = $request->withCookie('type', 'chocolate chip');

        $this->assertNotSame($request, $modified);
        $this->assertNull($request->cookie('type'));
        $this->assertSame([], $request->cookie());
        $this->assertSame('chocolate chip', $modified->cookie('type'));
        $this->assertSame(['type' => 'chocolate chip'], $modified->cookie());
        $this->assertNull($modified->withCookie('type', null)->cookie('type'));
    }

    /**
     * Test capturing the request
     */
    public function testCapture()
    {
        $request = Request::capture(
            ['HTTP_HOST' => 'example.com', 'REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/page/create'],
            ['title' => 'about'],
            [],
            ['User-Agent' => 'Test/1.0'],
            ['type' => 'chocolate chip']
        );

        $this->assertSame('POST', $request->method());
        $this->assertSame('http://example.com/page/create', (string) $request->url());
        $this->assertSame(['title' => 'about'], $request->post());
        $this->assertSame([], $request->files());
        $this->assertSame('Test/1.0', $request->header('User-Agent'));
        $this->assertSame('chocolate chip', $request->cookie('type'));
    }
}
