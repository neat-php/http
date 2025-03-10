<?php

namespace Neat\Http\Test;

use Neat\Http\Url;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UrlTest extends TestCase
{
    public function testPsr(): void
    {
        $psr = $this->getMockForAbstractClass(UriInterface::class);

        $url = new Url($psr);

        $this->assertSame($psr, $url->psr());
    }

    public function provideParts(): array
    {
        return [
            ['getScheme', 'http', 'scheme', 'http'],
            ['getScheme', 'https', 'scheme', 'https'],
            ['getScheme', 'http', 'isSecure', false],
            ['getScheme', 'https', 'isSecure', true],
            ['getUserInfo', '', 'username', null],
            ['getUserInfo', '', 'password', null],
            ['getUserInfo', 'john', 'username', 'john'],
            ['getUserInfo', 'john', 'password', null],
            ['getUserInfo', 'john:secret', 'username', 'john'],
            ['getUserInfo', 'john:secret', 'password', 'secret'],
            ['getHost', '', 'host', ''],
            ['getHost', 'localhost', 'host', 'localhost'],
            ['getPort', null, 'port', null],
            ['getPort', 8080, 'port', 8080],
            ['getPath', '', 'path', ''],
            ['getPath', '/', 'path', '/'],
            ['getPath', 'rootless', 'path', 'rootless'],
            ['getPath', '/rooted', 'path', '/rooted'],
            ['getQuery', '', 'query', ''],
            ['getQuery', 'key=value', 'query', 'key=value'],
            ['getFragment', '', 'fragment', ''],
            ['getFragment', 'anchor', 'fragment', 'anchor'],
            ['__toString', 'relative', '__toString', 'relative'],
            ['__toString', 'http://localhost/', '__toString', 'http://localhost/'],
            ['__toString', 'https://example.com/path?with=query#and-fragment', '__toString', 'https://example.com/path?with=query#and-fragment'],
            ['__toString', 'relative', 'get', 'relative'],
            ['__toString', 'http://localhost/', 'get', 'http://localhost/'],
            ['__toString', 'https://example.com/path?with=query#and-fragment', 'get', 'https://example.com/path?with=query#and-fragment'],
        ];
    }

    /**
     * @param mixed  $psrValue
     * @param mixed  $value
     * @dataProvider provideParts
     */
    public function testPart(string $psrMethod, $psrValue, string $method, $value): void
    {
        $psr = $this->getMockForAbstractClass(UriInterface::class);
        $psr->expects($this->once())->method($psrMethod)->willReturn($psrValue);

        $url = new Url($psr);

        $this->assertSame($value, $url->$method());
    }

    public function provideAuthorities(): array
    {
        return [
            ['localhost', null, '', 'localhost'],
            ['localhost', 8080, '', 'localhost:8080'],
            ['localhost', null, 'john:secret', 'john:secret@localhost'],
            ['localhost', 8080, 'john:secret', 'john:secret@localhost:8080'],
            ['', null, '', ''],
            ['', 8080, '', ''],
            ['', null, 'john:secret', ''],
            ['', 8080, 'john:secret', ''],
        ];
    }

    /**
     * @dataProvider provideAuthorities
     */
    public function testAuthority(string $host, ?int $port, string $userInfo, string $authority)
    {
        $psr = $this->getMockForAbstractClass(UriInterface::class);
        $psr->expects($this->once())->method('getHost')->willReturn($host);
        if ($host) {
            $psr->expects($this->once())->method('getUserInfo')->willReturn($userInfo);
            $psr->expects($this->once())->method('getPort')->willReturn($port);
        }

        $url = new Url($psr);

        $this->assertSame($authority, $url->authority());
    }

    public function provideWithParts(): array
    {
        return [
            ['withScheme', 'http', 'withScheme', 'http'],
            ['withScheme', 'https', 'withScheme', 'https'],
            ['withHost', '', 'withHost', ''],
            ['withHost', 'localhost', 'withHost', 'localhost'],
            ['withPort', null, 'withPort', null],
            ['withPort', 8080, 'withPort', 8080],
            ['withPath', '', 'withPath', ''],
            ['withPath', '/', 'withPath', '/'],
            ['withPath', 'rootless', 'withPath', 'rootless'],
            ['withPath', '/rooted', 'withPath', '/rooted'],
            ['withQuery', '', 'withQuery', ''],
            ['withQuery', 'key=value', 'withQuery', 'key=value'],
            ['withFragment', '', 'withFragment', ''],
            ['withFragment', 'anchor', 'withFragment', 'anchor'],
        ];
    }

    /**
     * @param mixed  $psrValue
     * @param mixed  $value
     * @dataProvider provideWithParts
     */
    public function testWithPart(string $psrMethod, $psrValue, string $method, $value): void
    {
        $psr2 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1->expects($this->once())->method($psrMethod)->with($psrValue)->willReturn($psr2);

        $url = new Url($psr1);

        /** @var Url $new */
        $new = $url->$method($value);

        $this->assertSame($psr2, $new->psr());
    }

    public function testWithUsername(): void
    {
        $psr2 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1->expects($this->once())->method('withUserInfo')->with('jane', $this->isNull())->willReturn($psr2);

        $url = new Url($psr1);

        $new = $url->withUserInfo('jane');

        $this->assertSame($psr2, $new->psr());
    }

    public function testWithUserInfo(): void
    {
        $psr2 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1->expects($this->once())->method('withUserInfo')->with('john', 'secret')->willReturn($psr2);

        $url = new Url($psr1);

        $new = $url->withUserInfo('john', 'secret');

        $this->assertSame($psr2, $new->psr());
    }

    public function testWithQueryParameters(): void
    {
        $psr2 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1 = $this->getMockForAbstractClass(UriInterface::class);
        $psr1->expects($this->at(0))->method('getQuery')->willReturn('key=value');
        $psr1->expects($this->at(1))->method('withQuery')->with('key=value&foo=bar')->willReturn($psr2);

        $url = new Url($psr1);

        $new = $url->withQueryParameters(['foo' => 'bar']);

        $this->assertSame($psr2, $new->psr());
    }
}
