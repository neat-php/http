<?php

namespace Neat\Http\Test;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neat\Http\Header;
use Neat\Http\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class MessageTest extends TestCase
{
    public function testDefaults(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage->expects($this->at(0))->method('getProtocolVersion')->willReturn('1.1');
        $psrMessage->expects($this->at(1))->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getContents')->willReturn('');
        $psrMessage->expects($this->at(2))->method('getHeaders')->willReturn([]);
        $psrMessage->expects($this->at(3))->method('getHeader')->willReturn([]);
        $psrMessage->expects($this->at(4))->method('getHeaders')->willReturn([]);
        $psrMessage->expects($this->at(5))->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->at(1))->method('getContents')->willReturn('');

        $message = new MessageMock($psrMessage);

        $this->assertSame('1.1', $message->version());
        $this->assertSame('', $message->body());

        $this->assertSame([], $message->headers());
        $this->assertNull($message->header('X-Test'));

        $this->assertSame("\r\n", Message::EOL);
        $this->assertSame("\r\n", (string)$message);
    }

    public function testVersionMutation(): void
    {
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage->expects($this->at(0))->method('withProtocolVersion')->with('1.0')->willReturn($psrMessage2);
        $psrMessage->expects($this->at(1))->method('getProtocolVersion')->willReturn('1.1');
        $psrMessage2->expects($this->at(0))->method('getProtocolVersion')->willReturn('1.0');

        $message = new MessageMock($psrMessage);
        $mutated = $message->withVersion('1.0');

        $this->assertNotSame($message, $mutated);
        $this->assertEquals('1.1', $message->version());
        $this->assertEquals('1.0', $mutated->version());
    }

    public function testAddedHeader(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage
            ->expects($this->at(0))->method('withHeader')->with('Host', ['example.com'])->willReturn($psrMessage2);
        $psrMessage->expects($this->at(1))->method('getHeader')->with('Host')->willReturn(['example.net']);
        $psrMessage2->expects($this->at(0))->method('getHeader')->with('Host')->willReturn(['example.com']);
        $psrMessage2->expects($this->at(1))->method('getHeader')->with('host')->willReturn(['example.com']);
        $psrMessage2->expects($this->at(2))->method('getHeaders')->willReturn(['Host' => ['example.com']]);
        $psrMessage2->expects($this->at(3))->method('getHeaders')->willReturn(['Host' => ['example.com']]);
        $psrMessage2->expects($this->at(4))->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getContents')->willReturn('');

        $message = new MessageMock($psrMessage);
        $mutated = $message->withHeader('Host', 'example.com');

        $this->assertNotSame($message, $mutated);
        $this->assertEquals(new Header('Host', 'example.net'), $message->header('Host'));
        $this->assertEquals(new Header('Host', 'example.com'), $mutated->header('Host'));
        $this->assertEquals(new Header('host', 'example.com'), $mutated->header('host'));
        $this->assertEquals([new Header('Host', 'example.com')], $mutated->headers());
        $this->assertSame("Host: example.com\r\n\r\n", (string)$mutated);
    }

    public function testWithAddedHeader(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage
            ->expects($this->at(0))->method('withAddedHeader')->with('Host', 'example.net')->willReturn($psrMessage2);
        $psrMessage->expects($this->at(1))->method('getHeader')->with('Host')->willReturn(['example.com']);
        $psrMessage->expects($this->at(2))->method('getHeaders')->willReturn(['Host' => ['example.com']]);
        $psrMessage2->expects($this->at(0))->method('getHeader')->with('Host')->willReturn(['example.com']);
        $psrMessage2
            ->expects($this->at(1))->method('getHeaders')->willReturn(['Host' => ['example.com', 'example.net']]);
        $psrMessage2
            ->expects($this->at(2))->method('getHeaders')->willReturn(['Host' => ['example.com', 'example.net']]);
        $psrMessage2->expects($this->at(3))->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getContents')->willReturn('');

        $message = new MessageMock($psrMessage);
        $mutated = $message->withAddedHeader('Host', 'example.net');

        $this->assertNotSame($message, $mutated);
        $this->assertEquals(new Header('Host', 'example.com'), $message->header('Host'));
        $this->assertEquals(new Header('Host', 'example.com'), $mutated->header('Host'));
        $this->assertEquals([new Header('Host', 'example.com')], $message->headers());
        $this->assertEquals([new Header('Host', 'example.com', 'example.net')], $mutated->headers());
        $this->assertSame("Host: example.com,example.net\r\n\r\n", (string)$mutated);
    }

    public function testRemovedHeader(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage->expects($this->at(0))->method('withoutHeader')->with('Host')->willReturn($psrMessage2);
        $psrMessage->expects($this->at(1))->method('getHeader')->with('Host')->willReturn(['example.com']);
        $psrMessage2->expects($this->any())->method('getHeader')->with('Host')->willReturn([]);
        $psrMessage2->expects($this->any())->method('getHeaders')->willReturn([]);
        $psrMessage2->expects($this->any())->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->once())->method('getContents')->willReturn('');

        $message = new MessageMock($psrMessage);
        $mutated = $message->withoutHeader('Host');

        $this->assertNotSame($message, $mutated);
        $this->assertEquals(new Header('Host', 'example.com'), $message->header('Host'));
        $this->assertNull($mutated->header('Host'));
        $this->assertEquals([], $mutated->headers());
        $this->assertSame("\r\n", (string)$mutated);
    }

    public function testBody(): void
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage->expects($this->at(0))->method('withBody')->with($psrStream)->willReturn($psrMessage2);
        $psrMessage2->expects($this->any())->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->any())->method('getContents')->willReturn('Hello world!');
        $psrMessage2->expects($this->once())->method('getHeaders')->willReturn([]);

        $message = new MessageMock($psrMessage);
        $mutated = $message->withBody($psrStream);

        $this->assertNotSame($message, $mutated);
        $this->assertSame('Hello world!', $mutated->body());
        $this->assertSame("\r\nHello world!", (string)$mutated);
    }

    public function testBodyStream(): void
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [], 'Hello world!'));

        $this->assertInstanceOf(StreamInterface::class, $message->bodyStream());
        $this->assertSame('Hello world!', $message->body());
    }
}
