<?php

namespace Neat\Http\Test;

use Neat\Http\Header;
use Neat\Http\Response;
use Neat\Http\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    public function testEmpty(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);

        $psrResponse->method('getBody')->willReturn($psrStream);
        $psrStream->method('getContents')->willReturn('');
        $psrStream->method('getSize')->willReturn(0);
        $psrResponse->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse->method('getHeaders')->willReturn([]);

        $response = new Response($psrResponse);

        $this->assertSame($psrResponse, $response->psr());
        $this->assertSame('', $response->body());
        $this->assertSame(204, $response->status()->code());
        $this->assertSame("HTTP/1.1 204 No Content\r\n\r\n", (string) $response);
    }

    public function testString(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);

        $psrResponse->method('getBody')->willReturn($psrStream);
        $psrStream->method('getContents')->willReturn('Hello world!');
        $psrStream->method('getSize')->willReturn(11);
        $psrResponse->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse->method('getHeaders')->willReturn([]);

        $response = new Response($psrResponse);

        $this->assertSame('Hello world!', $response->body());
        $this->assertSame(200, $response->status()->code());
        $this->assertSame("HTTP/1.1 200 OK\r\n\r\nHello world!", (string) $response);
    }

    public function testJson(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);

        $psrResponse->method('getBody')->willReturn($psrStream);
        $psrStream->method('getContents')->willReturn('{"json":true}');
        $psrStream->method('getSize')->willReturn(13);
        $psrResponse->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);
        $psrResponse->method('getHeader')->with('Content-Type')->willReturn(['application/json']);

        $response = new Response($psrResponse);

        $this->assertSame('{"json":true}', $response->body());
        $this->assertEquals(new Header('Content-Type', 'application/json'), $response->header('Content-Type'));
        $this->assertSame(200, $response->status()->code());
        $this->assertSame("HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n{\"json\":true}",
            (string) $response);
    }

    public function testStatusCode(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse2 */
        $psrResponse2 = clone $psrResponse;

        $psrResponse->method('getStatusCode')->willReturn(404);
        $psrResponse->method('getReasonPhrase')->willReturn('Not Found');
        $psrResponse->method('withStatus')->with(500)->willReturn($psrResponse2);
        $psrResponse->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse->method('getBody')->willReturn($psrStream);
        $psrResponse->method('getHeaders')->willReturn([]);

        $psrResponse2->method('getStatusCode')->willReturn(500);
        $psrResponse2->method('getReasonPhrase')->willReturn('Internal Server Error');
        $psrResponse2->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse2->method('getBody')->willReturn($psrStream);
        $psrResponse2->method('getHeaders')->willReturn([]);

        $psrStream->method('getContents')->willReturn('');

        $response = new Response($psrResponse);
        $mutated  = $response->withStatus(500);

        $this->assertNotSame($response, $mutated);
        $this->assertSame('', $response->body());
        $this->assertSame(404, $response->status()->code());
        $this->assertSame("HTTP/1.1 404 Not Found\r\n\r\n", (string) $response);

        $this->assertSame(500, $mutated->status()->code());
        $this->assertSame("HTTP/1.1 500 Internal Server Error\r\n\r\n", (string) $mutated);
    }

    public function testStatus(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse2 */
        $psrResponse2 = clone $psrResponse;

        $before = new Status(404, 'Are you lost?');
        $psrResponse->method('getStatusCode')->willReturn(404);
        $psrResponse->method('getReasonPhrase')->willReturn('Are you lost?');
        $psrResponse->expects($this->once())->method('withStatus')->with(403,
            'You shall not pass!')->willReturn($psrResponse2);
        $psrResponse->method('getBody')->willReturn($psrStream);
        $psrResponse->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse->method('getHeaders')->willReturn([]);

        $psrResponse2->method('getBody')->willReturn($psrStream);
        $psrResponse2->method('getStatusCode')->willReturn(403);
        $psrResponse2->method('getReasonPhrase')->willReturn('You shall not pass!');
        $psrResponse2->method('getProtocolVersion')->willReturn('1.1');
        $psrResponse2->method('getHeaders')->willReturn([]);

        $psrStream->method('getContents')->willReturn('');
        $psrStream->method('getSize')->willReturn(0);


        $response = new Response($psrResponse);
        $mutated  = $response->withStatus($after = new Status(403, 'You shall not pass!'));

        $this->assertSame('', $response->body());
        $this->assertEquals($before, $response->status());
        $this->assertSame(404, $response->status()->code());
        $this->assertSame("HTTP/1.1 404 Are you lost?\r\n\r\n", (string) $response);

        $this->assertEquals($after, $mutated->status());
        $this->assertSame(403, $mutated->status()->code());
        $this->assertSame("HTTP/1.1 403 You shall not pass!\r\n\r\n", (string) $mutated);
    }
}
