<?php

namespace Neat\Http\Test\Header;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neat\Http\Header\Authorization;
use Neat\Http\Test\MessageMock;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    public function testBasic(): void
    {
        $header = new Authorization(Authorization::TYPE_BASIC, 'top:secret');

        $this->assertSame('Basic', $header->type());
        $this->assertTrue($header->isBasic());
        $this->assertFalse($header->isBearer());
        $this->assertSame('top:secret', $header->credentials());
    }

    public function testBearer(): void
    {
        $header = new Authorization(Authorization::TYPE_BEARER, 'credentials');

        $this->assertSame('Bearer', $header->type());
        $this->assertFalse($header->isBasic());
        $this->assertTrue($header->isBearer());
        $this->assertSame('credentials', $header->credentials());
    }

    public function testMessage(): void
    {
        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost')));
        $this->assertNull($message->authorization());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Authorization' => ['Unknown credentials'],
        ]));
        $this->assertNull($message->authorization());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Authorization' => ['Missing'],
        ]));
        $this->assertNull($message->authorization());

        $message = new MessageMock(new ServerRequest('POST', new Uri('https://localhost'), [
            'Authorization' => ['Basic credentials'],
        ]));

        $authorization = $message->authorization();
        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertTrue($authorization->isBasic());
        $this->assertFalse($authorization->isBearer());
        $this->assertSame('credentials', $authorization->credentials());
        $this->assertSame('Basic', $authorization->type());

        $message = $message->withAuthorization('Bearer', 'HelloWorld');
        $authorization = $message->authorization();
        $this->assertFalse($authorization->isBasic());
        $this->assertTrue($authorization->isBearer());
        $this->assertSame('Bearer', $authorization->type());
        $this->assertSame('HelloWorld', $authorization->credentials());
    }
}
