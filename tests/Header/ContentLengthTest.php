<?php

namespace Neat\Http\Test\Header;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neat\Http\Header\ContentLength;
use Neat\Http\Test\MessageMock;
use PHPUnit\Framework\TestCase;

class ContentLengthTest extends TestCase
{
    public function testMinimal(): void
    {
        $header = new ContentLength(6);

        $this->assertSame(6, $header->length());
    }

    public function testMessage(): void
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost')));
        $this->assertNull($message->contentLength());

        $message = new MessageMock(
            new ServerRequest('POST', new Uri('https://localhost'), ['Content-Length' => ['6']])
        );

        $contentLength = $message->contentLength();

        $this->assertInstanceOf(ContentLength::class, $contentLength);
        $this->assertSame(6, $contentLength->length());

        $message       = $message->withContentLength(70);
        $contentLength = $message->contentLength();
        $this->assertInstanceOf(ContentLength::class, $contentLength);
        $this->assertSame(70, $contentLength->length());
    }
}
