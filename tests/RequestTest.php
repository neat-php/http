<?php

namespace Neat\Http\Test;

use Neat\Http\Header;
use Neat\Http\Request;
use Neat\Http\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    public function testEmpty(): void
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

        $request = new Request($psrRequest);

        $this->assertSame($psrRequest, $request->psr());
        $this->assertSame('', $request->body());
        $this->assertSame('', (string)$request->url());
        $this->assertSame('GET', $request->method());
    }

    public function testGet(): void
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
        $psrUri->expects($this->any())->method('__toString')->willReturn('http://localhost/');
        $psrUri->expects($this->any())->method('getPath')->willReturn('/');
        $psrUri->expects($this->any())->method('getQuery')->willReturn('');
        $psrRequest->expects($this->any())->method('getMethod')->willReturn('GET');
        $psrRequest->expects($this->any())->method('getHeaders')->willReturn([]);
        $psrRequest->expects($this->any())->method('getBody')->willReturn($psrStream);

        $request = new Request($psrRequest);

        $this->assertSame('', $request->body());
        $this->assertSame('http://localhost/', (string)$request->url());
        $this->assertSame('GET', $request->method());
        $this->assertSame("GET / HTTP/1.1\r\n\r\n", (string)$request);
    }

    public function testPost(): void
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
        $psrUri->expects($this->any())->method('__toString')->willReturn('https://localhost/resource?id=1');
        $psrUri->expects($this->any())->method('getPath')->willReturn('/resource');
        $psrUri->expects($this->any())->method('getQuery')->willReturn('id=1');

        $request = new Request($psrRequest);

        $this->assertSame('{"json":true}', $request->body());
        $this->assertEquals(new Header('Content-Type', 'application/json'), $request->header('Content-Type'));
        $this->assertSame('https://localhost/resource?id=1', (string)$request->url());
        $this->assertSame('POST', $request->method());
        $this->assertSame(
            "POST /resource?id=1 HTTP/1.1\r\nContent-Type: application/json\r\n\r\n{\"json\":true}",
            (string)$request,
        );
    }

    public function testWithMethod(): void
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

    public function testWithUrl(): void
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

        $url = new Url($psrUri);

        $this->assertEquals($url, $request->withUrl($url)->url());
    }
}
