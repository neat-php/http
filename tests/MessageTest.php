<?php
namespace Neat\Test\Http;

use Neat\Http\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * Test defaults
     */
    public function testDefaults()
    {
        $message = new Message;

        $this->assertSame('1.1', $message->version());
        $this->assertNull($message->body());

        $this->assertSame([], $message->headers());
        $this->assertNull($message->header('X-Test'));
        $this->assertSame('DEFAULT', $message->header('X-Test', 'DEFAULT'));

        $this->assertSame("\r\n", Message::EOL);
        $this->assertSame("\r\n", (string) $message);
    }

    /**
     * Test version mutation
     */
    public function testVersionMutation()
    {
        $message = new Message;
        $mutated = $message->withVersion('1.0');

        $this->assertNotSame($message, $mutated);
        $this->assertEquals('1.1', $message->version());
        $this->assertEquals('1.0', $mutated->version());
    }

    /**
     * Test message with added header
     */
    public function testAddedHeader()
    {
        $message = new Message;
        $mutated = $message->withHeader('Host', 'example.com');

        $this->assertNotSame($message, $mutated);
        $this->assertSame('example.com', $mutated->header('Host'));
        $this->assertSame('example.com', $mutated->header('host'));
        $this->assertSame(['Host' => 'example.com'], $mutated->headers());
        $this->assertSame("Host: example.com\r\n\r\n", (string) $mutated);
    }

    /**
     * Test message with modified header
     */
    public function testModifiedHeader()
    {
        $message = (new Message)->withHeader('Host', 'example.com');
        $mutated = $message->withHeader('host', 'example.net');

        $this->assertSame('example.net', $mutated->header('Host'));
        $this->assertSame('example.net', $mutated->header('host'));
        $this->assertSame(['host' => 'example.net'], $mutated->headers());
        $this->assertSame("host: example.net\r\n\r\n", (string) $mutated);
    }

    /**
     * Test message with removed header
     */
    public function testRemovedHeader()
    {
        $message = (new Message)->withHeader('Host', 'example.com');
        $mutated = $message->withoutHeader('host');

        $this->assertNotSame($message, $mutated);
        $this->assertNull($mutated->header('Host'));
        $this->assertNull($mutated->header('host'));
        $this->assertSame([], $mutated->headers());
        $this->assertSame("\r\n", (string) $mutated);
    }

    /**
     * Test message with body
     */
    public function testBody()
    {
        $message = new Message;
        $mutated = $message->withBody('Hello world!');

        $this->assertNotSame($message, $mutated);
        $this->assertSame('Hello world!', $mutated->body());
        $this->assertSame("\r\nHello world!", (string) $mutated);
    }
}
