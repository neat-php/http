<?php

namespace Neat\Test\Http;

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
}
