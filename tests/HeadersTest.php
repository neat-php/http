<?php
namespace Neat\Test\Http;

use Neat\Http\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    /**
     * Test custom status
     */
    public function testEmpty()
    {
        $headers = new Headers;

        $this->assertSame([], $headers->all());
        $this->assertSame([], $headers->lines());
        $this->assertSame('', (string) $headers);
        $this->assertFalse($headers->has('X-Unknown'));
        $this->assertNull($headers->get('X-Unknown'));
        $this->assertSame('Foo', $headers->get('X-Unknown', 'Foo'));
        $this->assertSame(0, count($headers));
        $this->assertSame([], iterator_to_array($headers));
    }

    public function testPreset()
    {
        $headers = Headers::capture(['Content-Type' => 'text/html']);

        $this->assertSame(['Content-Type' => 'text/html'], $headers->all());
        $this->assertSame(['Content-Type: text/html'], $headers->lines());
        $this->assertSame("Content-Type: text/html\r\n", (string) $headers);
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertSame('text/html', $headers->get('Content-Type'));
        $this->assertSame(1, count($headers));
        $this->assertSame(['Content-Type' => 'text/html'], iterator_to_array($headers));
    }

    public function testSetHeaders()
    {
        $headers = new Headers;

        $headers->set('Content-Type', 'text/html');
        $this->assertSame(['Content-Type' => 'text/html'], $headers->all());

        $headers->set('Host', 'example.com');
        $this->assertSame(['Content-Type' => 'text/html', 'Host' => 'example.com'], $headers->all());

        $headers->set('Content-Type', 'text/plain');
        $this->assertSame(['Host' => 'example.com', 'Content-Type' => 'text/plain'], $headers->all());
    }

    public function testCaseInsensitivity()
    {
        $headers = new Headers(['Content-Type' => 'text/html']);

        $this->assertTrue($headers->has('CONTENT-TYPE'));
        $this->assertSame('text/html', $headers->get('CONTENT-TYPE'));

        $this->assertTrue($headers->has('content-type'));
        $this->assertSame('text/html', $headers->get('content-type'));

        $headers->set('content-type', 'text/plain');
        $this->assertSame(['content-type' => 'text/plain'], $headers->all());
    }
}
