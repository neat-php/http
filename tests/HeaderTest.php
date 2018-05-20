<?php

namespace Neat\Http\Test;

use Neat\Http\Header;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    /**
     * Test custom status
     */
    public function testHeader()
    {
        $header = new Header('Content-Type', 'text/html');

        $this->assertSame('Content-Type', $header->name());
        $this->assertSame('text/html', $header->value());
        $this->assertSame('Content-Type: text/html', $header->line());
        $this->assertSame('Content-Type: text/html', (string) $header);
    }

    /**
     * Test capture empty headers array
     */
    public function testCaptureEmpty()
    {
        $headers = Header::capture([]);

        $this->assertSame([], $headers);
    }

    /**
     * Test capture
     */
    public function testCapture()
    {
        $headers = Header::capture([
            'Content-Type'   => 'application/json',
            'Content-Length' => 123,
        ]);

        $this->assertEquals([
            'content-type'   => new Header('Content-Type', 'application/json'),
            'content-length' => new Header('Content-Length', 123),
        ], $headers);
    }
}
