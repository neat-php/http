<?php

namespace Neat\Http\Test;

use Neat\Http\Header;
use Neat\Http\Request;
use Neat\Http\Upload;
use Neat\Http\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    /**
     * Test empty request
     */
    public function testEmpty()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var UriInterface|MockObject $psrUri */
        $psrUri = $this->getMockForAbstractClass(UriInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);

        $psrRequest->expects($this->at(0))->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getContents')->willReturn('');
        $psrRequest->expects($this->at(1))->method('getUri')->willReturn($psrUri);
        $psrRequest->expects($this->at(2))->method('getMethod')->willReturn('GET');
        $psrRequest->expects($this->at(3))->method('getQueryParams')->willReturn([]);
        $psrRequest->expects($this->at(4))->method('getParsedBody')->willReturn([]);
        $psrRequest->expects($this->at(5))->method('getCookieParams')->willReturn([]);
//        $psrRequest->expects($this->at(6))->method('getParsedBody')->willReturn([]);

        $request = new Request($psrRequest);

        $this->assertSame('', $request->body());
        $this->assertSame('', (string) $request->url());
        $this->assertsame('GET', $request->method());
        $this->assertSame([], $request->query());
        $this->assertSame([], $request->post());
//        @TODO
//        $this->assertSame([], $request->files());
        $this->assertSame([], $request->cookie());
    }

    /**
     * Test GET request
     */
    public function testGet()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var UriInterface|MockObject $psrStream */
        $psrUri = $this->getMockForAbstractClass(UriInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);

        $psrRequest->expects($this->at(0))->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getContents')->willReturn('');
        $psrRequest->expects($this->exactly(2))->method('getUri')->willReturn($psrUri);
        $psrRequest->expects($this->any())->method('getProtocolVersion')->willReturn('1.1');
        $psrUri->expects($this->any())->method('getScheme')->willReturn('http');
        $psrUri->expects($this->any())->method('getHost')->willReturn('localhost');
        $psrUri->expects($this->any())->method('getPath')->willReturn('/');
        $psrRequest->expects($this->any())->method('getMethod')->willReturn('GET');
        $psrRequest->expects($this->any())->method('getHeaders')->willReturn([]);
        $psrRequest->expects($this->any())->method('getBody')->willReturn($psrStream);

        $request = new Request($psrRequest);
//        $request = new Request('GET', 'http://localhost/');

        $this->assertSame('', $request->body());
        $this->assertSame('http://localhost/', (string) $request->url());
        $this->assertsame('GET', $request->method());
        $this->assertsame("GET / HTTP/1.1\r\n\r\n", (string) $request);
    }

    /**
     * Test POST request
     */
    public function testPost()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var UriInterface|MockObject $psrUri */
        $psrUri = $this->getMockForAbstractClass(UriInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);

        $psrRequest->expects($this->any())->method('getMethod')->willReturn('POST');
        $psrRequest->expects($this->any())->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->any())->method('getContents')->willReturn('{"json":true}');
        $psrRequest->expects($this->any())->method('getParsedBody')->willReturn(['json' => true]);
        $psrRequest->expects($this->any())->method('getHeader')->willReturn(['application/json']);
        $psrRequest->expects($this->any())->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);
        $psrRequest->expects($this->any())->method('getProtocolVersion')->willReturn('1.1');
        $psrRequest->expects($this->any())->method('getUri')->willReturn($psrUri);
        $psrUri->method('getScheme')->willReturn('https');
        $psrUri->method('getHost')->willReturn('localhost');
        $psrUri->method('getPath')->willReturn('/resource');
        $psrUri->method('getQuery')->willReturn('id=1');

        $request = new Request($psrRequest);
//        $request    = new Request('POST', 'https://localhost/resource?id=1', ['json' => true]);

        $this->assertSame('{"json":true}', (string) $request->body());
        $this->assertSame(['json' => true], $request->post());
        $this->assertSame(true, $request->post('json'));
        $this->assertNull($request->post('unknown'));
        $this->assertEquals(new Header('Content-Type', 'application/json'), $request->header('Content-Type'));
        $this->assertSame('https://localhost/resource?id=1', (string) $request->url());
        $this->assertsame('POST', $request->method());
        $this->assertsame("POST /resource?id=1 HTTP/1.1\r\nContent-Type: application/json\r\n\r\n{\"json\":true}",
            (string) $request);
    }

    /**
     * Test with files
     */
    public function testWithFiles()
    {
        /** @var UploadedFileInterface|MockObject $psrUpload */
        $psrUpload = $this->getMockForAbstractClass(UploadedFileInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest2 */
        $psrRequest2 = clone $psrRequest;

        $psrRequest->expects($this->at(0))->method('withUploadedFiles')->with(['avatar' => $psrUpload])->willReturn($psrRequest2);
        $psrRequest2->expects($this->at(0))->method('getUploadedFiles')->willReturn(['avatar' => $psrUpload]);
        $psrRequest2->expects($this->at(1))->method('getUploadedFiles')->willReturn(['avatar' => $psrUpload]);

        $request = new Request($psrRequest);

        $mutated = $request->withFiles(['avatar' => new Upload($psrUpload)]);

        $this->assertInstanceOf(Upload::class, $mutated->files('avatar'));
        $this->assertEquals(['avatar' => new Upload($psrUpload)], $mutated->files());
    }

    /**
     * Test with method
     */
    public function testWithMethod()
    {
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest2 */
        $psrRequest2 = clone $psrRequest;

        $psrRequest->expects($this->at(0))->method('withMethod')->with('POST')->willReturn($psrRequest2);
        $psrRequest2->expects($this->at(0))->method('getMethod')->willReturn('POST');

        $request = new Request($psrRequest);

        $this->assertSame('POST', $request->withMethod('POST')->method());
    }

    /**
     * Test with URL
     */
    public function testWithUrl()
    {
        /** @var UriInterface|MockObject $psrUri */
        $psrUri = $this->getMockForAbstractClass(UriInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest2 */
        $psrRequest2 = clone $psrRequest;

        $psrRequest->expects($this->at(0))->method('withUri')->with($psrUri)->willReturn($psrRequest2);
        $psrRequest2->expects($this->at(0))->method('getUri')->willReturn($psrUri);

        $request = new Request($psrRequest);
//        $request    = new Request('GET', 'http://localhost/');
        $url = new Url($psrUri);

        $this->assertEquals($url, $request->withUrl($url)->url());
    }

    /**
     * Test query
     */
    public function testQuery()
    {
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);

        $psrRequest->expects($this->any())->method('getQueryParams')->willReturn([]);

        $empty = new Request($psrRequest);
//        $empty      = new Request('GET', '/');

        $this->assertNull($empty->query('id'));
        $this->assertEquals([], $empty->query());

        /** @var UriInterface|MockObject $psrUri */
        $psrUri = $this->getMockForAbstractClass(UriInterface::class);
        /** @var UriInterface|MockObject $psrUri2 */
        $psrUri2 = clone $psrUri;
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest2 */
        $psrRequest2 = clone $psrRequest;

        $psrRequest->expects($this->any())->method('getQueryParams')->willReturn(['id' => '1']);
        $psrRequest->expects($this->any())->method('getUri')->willReturn($psrUri);
        $psrUri->expects($this->once())->method('withQuery')->with('page=1')->willReturn($psrUri2);
        $psrRequest->expects($this->once())->method('withUri')->with($psrUri)->willReturn($psrRequest2);
        $psrRequest2->expects($this->any())->method('getQueryParams')->willReturn(['page' => '1']);
        $psrRequest2->expects($this->any())->method('getUri')->willReturn($psrUri2);
        $psrUri2->expects($this->any())->method('getQuery')->willReturn('page=1');

        $request = new Request($psrRequest);
//        $request    = new Request('GET', '?id=1');

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
        /** @var ServerRequestInterface|MockObject $psrRequest */
        $psrRequest = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var ServerRequestInterface|MockObject $psrRequest2 */
        $psrRequest2 = clone $psrRequest;
        $psrRequest3 = clone $psrRequest2;

        $psrRequest->method('getCookieParams')->willReturn([]);
        $psrRequest->expects($this->at(1))->method('withCookieParams')->with(['type' => 'chocolate chip'])->willReturn($psrRequest2);
        $psrRequest2->method('getCookieParams')->willReturn(['type' => 'chocolate chip']);
        $psrRequest2->method('withCookieParams')->with([])->willReturn($psrRequest3);
        $psrRequest3->method('getCookieParams')->willReturn([]);

        $request = new Request($psrRequest);

        $modified = $request->withCookie('type', 'chocolate chip');

        $this->assertNotSame($request, $modified);
        $this->assertNull($request->cookie('type'));
        $this->assertSame([], $request->cookie());
        $this->assertSame('chocolate chip', $modified->cookie('type'));
        $this->assertSame(['type' => 'chocolate chip'], $modified->cookie());
        $this->assertNull($modified->withCookie('type', null)->cookie('type'));
    }
}
