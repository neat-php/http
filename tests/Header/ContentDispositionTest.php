<?php

namespace Neat\Http\Test\Header;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neat\Http\Header\ContentDisposition;
use Neat\Http\Test\MessageMock;
use PHPUnit\Framework\TestCase;

class ContentDispositionTest extends TestCase
{
    /**
     * Test minimal
     */
    public function testMinimal()
    {
        $header = new ContentDisposition(ContentDisposition::INLINE);

        $this->assertSame('inline', $header->getValue());
        $this->assertSame('inline', $header->disposition());

        $this->assertNull($header->fieldname());
        $this->assertNull($header->getName());

        $this->assertNull($header->filename());
        $this->assertNull($header->getFilename());
    }

    /**
     * Test with names
     */
    public function testWithNames()
    {
        $header = new ContentDisposition(ContentDisposition::ATTACHMENT, 'filename.ext', 'FieldName');

        $this->assertSame('attachment', $header->getValue());
        $this->assertSame('attachment', $header->disposition());

        $this->assertSame('FieldName', $header->fieldname());
        $this->assertSame('FieldName', $header->getName());

        $this->assertSame('filename.ext', $header->filename());
        $this->assertSame('filename.ext', $header->getFilename());
    }

    /**
     * Test message
     */
    public function testMessage()
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost')));
        $this->assertNull($message->contentDisposition());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Content-Disposition' => ['inline'],
        ]));

        $header = $message->contentDisposition();

        $this->assertInstanceOf(ContentDisposition::class, $header);
        $this->assertSame('inline', $header->disposition());
        $this->assertNull($header->filename());
        $this->assertNull($header->fieldname());

        $message = $message->withContentDisposition('attachment', 'filename.ext', 'FieldName');
        $header  = $message->contentDisposition();
        $this->assertInstanceOf(ContentDisposition::class, $header);
        $this->assertSame('attachment', $header->disposition());
        $this->assertSame('filename.ext', $header->filename());
        $this->assertSame('FieldName', $header->fieldname());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Content-Disposition' => ['attachment; filename=photo.jpg; name=Name'],
        ]));
        $header  = $message->contentDisposition();
        $this->assertInstanceOf(ContentDisposition::class, $header);
        $this->assertSame('attachment', $header->disposition());
        $this->assertSame('photo.jpg', $header->filename());
        $this->assertSame('Name', $header->fieldname());
    }
}
