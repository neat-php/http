<?php

namespace Neat\Http\Test\Header;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neat\Http\Header\ContentType;
use Neat\Http\Test\MessageMock;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    /**
     * Test minimal
     */
    public function testMinimal()
    {
        $header = new ContentType('text/plain');

        $this->assertSame('text/plain', $header->type());
        $this->assertSame('text/plain', $header->getValue());

        $this->assertNull($header->charset());
        $this->assertNull($header->getCharset());

        $this->assertNull($header->boundary());
        $this->assertNull($header->getBoundary());
    }

    /**
     * Test with charset and boundary
     */
    public function testWithCharsetAndBoundary()
    {
        $header = new ContentType('application/json', 'utf-8', 'boundary');

        $this->assertSame('application/json', $header->type());
        $this->assertSame('application/json', $header->getValue());

        $this->assertSame('utf-8', $header->charset());
        $this->assertSame('utf-8', $header->getCharset());

        $this->assertSame('boundary', $header->boundary());
        $this->assertSame('boundary', $header->getBoundary());
    }

    /**
     * Test message
     */
    public function testMessage()
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost')));
        $this->assertNull($message->contentType());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Content-Type' => ['application/json'],
        ]));

        $contentType = $message->contentType();

        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertSame('application/json', $contentType->type());
        $this->assertNull($contentType->charset());
        $this->assertNull($contentType->boundary());

        $message     = $message->withContentType('multipart/form-data', 'utf-8', 'custom-boundary');
        $contentType = $message->contentType();
        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertSame('multipart/form-data', $contentType->type());
        $this->assertSame('utf-8', $contentType->charset());
        $this->assertSame('custom-boundary', $contentType->boundary());

        $message     = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Content-Type' => ['multipart/form-data; charset=utf-8; boundary=custom-boundary'],
        ]));
        $contentType = $message->contentType();
        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertSame('multipart/form-data', $contentType->type());
        $this->assertSame('utf-8', $contentType->charset());
        $this->assertSame('custom-boundary', $contentType->boundary());
    }
}
