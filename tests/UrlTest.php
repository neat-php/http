<?php
namespace Neat\Test\Http;

use PHPUnit\Framework\TestCase;
use Neat\Http\Url;

class UrlTest extends TestCase
{
    /**
     * Provide an empty and a full URL
     *
     * @return string[][]
     */
    public function provideEmptyAndFullUrl()
    {
        return [
            [''],
            ['http://john:secret@example.com:8080/path/to/page?filter=stuff#someanchor'],
        ];
    }

    /**
     * Provider normalized URL's
     *
     * @return string[][]
     */
    public function provideNormalizedUrls()
    {
        return [
            [''],
            ['https:'],
            ['//example.com'],
            ['/page-with-slash'],
            ['?page=2'],
            ['#anchor'],
            ['http://example.com/'],
            ['https://user:pass@example.com:8080/path/to/page?filter=stuff#someanchor'],
        ];
    }

    /**
     * Provider normalized URL's
     *
     * @return string[][]
     */
    public function provideNormalizableUrls()
    {
        return [
            ['HTTP:', 'http:'],
            ['HTTPS:', 'https:'],
            ['//EXAMPLE.COM/', '//example.com/'],
            ['http://example.com:80', 'http://example.com'],
            ['https://example.com:443', 'https://example.com'],
            ['page-without-slash', '/page-without-slash'],
        ];
    }

    /**
     * Provide invalid URL's
     *
     * @return string[][]
     */
    public function provideInvalidUrls()
    {
        return [
            ['http:///'],
        ];
    }

    /**
     * Provider URL's from server super global
     *
     * @return array
     */
    public function providerUrlsFromServer()
    {
        return [
            ['http:', []],
            ['http:', ['HTTPS' => 'off']],
            ['https:', ['HTTPS' => 'on']],
            ['http://example.com', ['HTTP_HOST' => 'example.com']],
            ['http://example.com:8080', ['HTTP_HOST' => 'example.com:8080']],
            ['http://example.com/page?query', ['HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/page?query']],
        ];
    }

    /**
     * Test get and set URL string representation
     *
     * @param string $url
     * @dataProvider provideNormalizedUrls
     */
    public function testGetAndSetStringRepresentation($url)
    {
        $this->assertEquals($url, (string) new Url($url));
    }

    /**
     * Test get and set URL string representation
     *
     * @param string $url
     * @param string $normalized
     * @dataProvider provideNormalizableUrls
     */
    public function testGetAndSetNormalizedStringRepresentation($url, $normalized)
    {
        $this->assertEquals($normalized, (string) new Url($url));
    }

    /**
     * Test get URL components
     */
    public function testGetUrlComponents()
    {
        $url = new Url('http://user:pass@example.com:8080/path/to/page?filter=stuff#anchor');

        $this->assertSame('http', $url->scheme());
        $this->assertSame('user', $url->username());
        $this->assertSame('pass', $url->password());
        $this->assertSame('example.com', $url->host());
        $this->assertSame(8080, $url->port());
        $this->assertSame('/path/to/page', $url->path());
        $this->assertSame('filter=stuff', $url->query());
        $this->assertSame('anchor', $url->fragment());
    }

    /**
     * Test get empty URL components
     */
    public function testGetEmptyUrlComponents()
    {
        $url = new Url('');

        $this->assertSame('', $url->scheme());
        $this->assertSame('', $url->username());
        $this->assertSame('', $url->password());
        $this->assertSame('', $url->host());
        $this->assertNull($url->port());
        $this->assertSame('', $url->path());
        $this->assertSame('', $url->query());
        $this->assertSame('', $url->fragment());
    }

    /**
     * Test get normalized URL components
     */
    public function testGetNormalizedUrlComponents()
    {
        $url = new Url('HTTP://EXAMPLE.COM:80/');

        $this->assertSame('http', $url->scheme());
        $this->assertSame('example.com', $url->host());
        $this->assertNull($url->port());
        $this->assertSame('http://example.com/', (string) $url);

        $url = new Url('HTTPS://EXAMPLE.COM:443/');

        $this->assertSame('https', $url->scheme());
        $this->assertNull($url->port());
        $this->assertSame('https://example.com/', (string) $url);
    }

    /**
     * Test URL with components
     *
     * @param string $url
     * @dataProvider provideEmptyAndFullUrl
     */
    public function testUrlWithComponents($url)
    {
        $url = new Url($url);

        $this->assertSame('https', $url->withScheme('https')->scheme());
        $this->assertSame('jane', $url->withUserInfo('jane')->username());
        $this->assertSame('qwerty', $url->withUserInfo('jane', 'qwerty')->password());
        $this->assertSame('host.test', $url->withHost('host.test')->host());
        $this->assertSame(800, $url->withPort(800)->port());
        $this->assertSame(800, $url->withPort('800')->port());
        $this->assertSame('/some/other/page', $url->withPath('/some/other/page')->path());
        $this->assertSame('id=1', $url->withQuery('id=1')->query());
        $this->assertSame('other-anchor', $url->withFragment('other-anchor')->fragment());
    }

    /**
     * Test URL without components
     *
     * @param string $url
     * @dataProvider provideEmptyAndFullUrl
     */
    public function testUrlWithoutComponents($url)
    {
        $url = new Url($url);

        $this->assertSame('', $url->withScheme('')->scheme());
        $this->assertSame('', $url->withUserInfo('')->username());
        $this->assertSame('', $url->withUserInfo('jane')->password());
        $this->assertSame('', $url->withHost('')->host());
        $this->assertNull($url->withPort()->port());
        $this->assertSame('', $url->withPath('')->path());
        $this->assertSame('', $url->withQuery('')->query());
        $this->assertSame('', $url->withFragment('')->fragment());
    }

    /**
     * Test captured URL's
     *
     * @param string $url
     * @param array  $server
     * @dataProvider providerUrlsFromServer
     */
    public function testCapturedUrlsFromServer($url, $server)
    {
        $this->assertEquals($url, Url::capture($server));
    }

    /**
     * Test invalid URL's
     *
     * @param string $url
     * @dataProvider provideInvalidUrls
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidUrls($url)
    {
        new Url($url);
    }

    /**
     * Test negative port number
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithNegativePort()
    {
        (new Url())->withPort(-1);
    }

    /**
     * Test port number above maximum
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithPortAboveMaximum()
    {
        (new Url())->withPort(65537);
    }
}
