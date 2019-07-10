<?php

namespace Neat\Http\Test;

use PHPUnit\Framework\TestCase;
use Neat\Http\StatusException;
use Neat\Http\Status;

class StatusExceptionTest extends TestCase
{
    /**
     * Test exception throwing
     */
    public function testThrowException()
    {
        $this->expectException(StatusException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not Found');

        throw new StatusException(404);
    }

    /**
     * Test default status
     */
    public function testDefaultStatus()
    {
        $exception = new StatusException(403);

        $this->assertInstanceOf(Status::class, $exception->status());

        $this->assertSame(403, $exception->status()->code());
        $this->assertSame(403, $exception->getCode());

        $this->assertSame('Forbidden', $exception->status()->reason());
        $this->assertSame('Forbidden', $exception->getMessage());
    }

    /**
     * Test custom status
     */
    public function testCustomStatus()
    {
        $exception = new StatusException(403, 'You Shall Not Pass!');

        $this->assertSame('You Shall Not Pass!', $exception->status()->reason());
        $this->assertSame('You Shall Not Pass!', $exception->getMessage());
    }
}
