<?php

namespace Neat\Http\Test;

use Neat\Http\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UrlTest extends TestCase
{
    /**
     * @param string $scheme
     * @return UriInterface|MockObject
     */
    private function mock(string $scheme = 'https'): UriInterface
    {
        $mock = $this->getMockForAbstractClass(UriInterface::class);
        $mock->method('getScheme')->willReturn($scheme);

        return $mock;
    }

    public function testScheme()
    {
        $url = new Url($this->mock('https'));
        $this->assertSame('https', $url->scheme());
        $url = new Url($this->mock('http'));
        $this->assertSame('http', $url->scheme());
    }

    public function testIsSecure()
    {
        $url = new Url($this->mock('https'));
        $this->assertTrue($url->isSecure());
        $url = new Url($this->mock('http'));
        $this->assertFalse($url->isSecure());
    }
}
