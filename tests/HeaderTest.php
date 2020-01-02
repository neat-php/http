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
        $this->assertSame('Content-Type: text/html', (string)$header);
    }

    public function testMultipleValues()
    {
        $header = new Header('Foo', 'bar', 'baz');

        $this->assertSame('bar,baz', $header->value());
        $this->assertSame('Foo: bar,baz', $header->line());
    }

    public function testHeaderNormalization()
    {
        $header = new Header('content-type', 'text/html');

        $this->assertSame('Content-Type', $header->name());
        $this->assertSame('Content-Type: text/html', $header->line());
    }

    public function testValueNormalization()
    {
        $header = new Header('Foo', ' bar', "baz\t");

        $this->assertSame('bar,baz', $header->value());
        $this->assertSame('Foo: bar,baz', $header->line());
    }
}
