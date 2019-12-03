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
    /**
     * Test defaults
     */
    public function testDefaults()
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
        $this->assertSame("\r\n", (string) $message);
    }

    /**
     * Test version mutation
     */
    public function testVersionMutation()
    {
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage  = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage->expects($this->at(0))->method('withProtocolVersion')->with($this->equalTo('1.0'))->willReturn($psrMessage2);
        $psrMessage->expects($this->at(1))->method('getProtocolVersion')->willReturn('1.1');
        $psrMessage2->expects($this->at(0))->method('getProtocolVersion')->willReturn('1.0');

        $message = new MessageMock($psrMessage);
        $mutated = $message->withVersion('1.0');

        $this->assertNotSame($message, $mutated);
        $this->assertEquals('1.1', $message->version());
        $this->assertEquals('1.0', $mutated->version());
    }

    /**
     * Test message with added header
     */
    public function testAddedHeader()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage  = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage->expects($this->at(0))->method('withHeader')
            ->with($this->equalTo('Host'), $this->equalTo('example.com'))->willReturn($psrMessage2);
        $psrMessage->expects($this->at(1))->method('getHeader')->with($this->equalTo('Host'))->willReturn(['example.net']);
        $psrMessage2->expects($this->at(0))->method('getHeader')->with($this->equalTo('Host'))->willReturn(['example.com']);
        $psrMessage2->expects($this->at(1))->method('getHeader')->with($this->equalTo('host'))->willReturn(['example.com']);
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
        $this->assertSame("Host: example.com\r\n\r\n", (string) $mutated);
    }

    /**
     * Test message with body
     */
    public function testBody()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var MessageInterface|MockObject $psrMessage */
        $psrMessage  = $this->getMockForAbstractClass(MessageInterface::class);
        $psrMessage2 = clone $psrMessage;
        $psrMessage->expects($this->at(0))->method('withBody')->with($psrStream)->willReturn($psrMessage2);
        $psrMessage2->expects($this->any())->method('getBody')->willReturn($psrStream);
        $psrStream->expects($this->any())->method('getContents')->willReturn('Hello world!');
        $psrMessage2->expects($this->once())->method('getHeaders')->willReturn([]);

        $message = new MessageMock($psrMessage);
        $mutated = $message->withBody($psrStream);

        $this->assertNotSame($message, $mutated);
        $this->assertSame('Hello world!', $mutated->body());
        $this->assertSame("\r\nHello world!", (string) $mutated);
    }

    public function testBodyStream()
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [], 'Hello world!'));

        $this->assertInstanceOf(StreamInterface::class, $message->bodyStream());
        $this->assertSame('Hello world!', (string) $message->body());
    }

    public function testAuthorization()
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost')));
        $this->assertNull($message->authorization());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'),
            ['Authorization' => ['Basic abcdef']]));

        $authorization = $message->authorization();
        $this->assertInstanceOf(Header\Authorization::class, $authorization);
        $this->assertTrue($authorization->isBasic());
        $this->assertFalse($authorization->isBearer());
        $this->assertSame('abcdef', $authorization->credentials());
        $this->assertSame('Basic', $authorization->type());

        $message       = $message->withAuthorization('Bearer', 'HelloWorld');
        $authorization = $message->authorization();
        $this->assertFalse($authorization->isBasic());
        $this->assertTrue($authorization->isBearer());
        $this->assertSame('Bearer', $authorization->type());
        $this->assertSame('HelloWorld', $authorization->credentials());
    }

    public function testContentType()
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost')));
        $this->assertNull($message->contentType());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'),
            ['Content-Type' => ['application/json; charset=utf-8']]));

        $contentType = $message->contentType();

        $this->assertInstanceOf(Header\ContentType::class, $contentType);
        $this->assertSame('application/json', $contentType->getValue());
        $this->assertSame('utf-8', $contentType->getCharset());

        $message     = $message->withContentType('plain/text', 'ascii');
        $contentType = $message->contentType();
        $this->assertSame('plain/text', $contentType->getValue());
        $this->assertSame('ascii', $contentType->getCharset());
    }
}
