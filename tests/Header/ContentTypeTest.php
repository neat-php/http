<?php

namespace Neat\Http\Test\Header;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neat\Http\Header\ContentType;
use Neat\Http\Test\MessageMock;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    public function testMinimal(): void
    {
        $header = new ContentType('text/plain');

        $this->assertSame('text/plain', $header->type());
        $this->assertNull($header->charset());
        $this->assertNull($header->boundary());
    }

    public function testWithCharsetAndBoundary(): void
    {
        $header = new ContentType('application/json', 'utf-8', 'boundary');

        $this->assertSame('application/json', $header->type());
        $this->assertSame('utf-8', $header->charset());
        $this->assertSame('boundary', $header->boundary());
    }

    public function testMessage(): void
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
