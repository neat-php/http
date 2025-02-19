<?php

namespace Neat\Http\Test;

use Neat\Http\Exception\StatusException;
use Neat\Http\Status;
use PHPUnit\Framework\TestCase;

class StatusExceptionTest extends TestCase
{
    public function testThrowException(): void
    {
        $this->expectException(StatusException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not Found');

        throw new StatusException(404);
    }

    public function testDefaultStatus(): void
    {
        $exception = new StatusException(403);

        $this->assertInstanceOf(Status::class, $exception->status());

        $this->assertSame(403, $exception->status()->code());
        $this->assertSame(403, $exception->getCode());

        $this->assertSame('Forbidden', $exception->status()->reason());
        $this->assertSame('Forbidden', $exception->getMessage());
    }

    public function testCustomStatus(): void
    {
        $exception = new StatusException(403, 'You Shall Not Pass!');

        $this->assertSame('You Shall Not Pass!', $exception->status()->reason());
        $this->assertSame('You Shall Not Pass!', $exception->getMessage());
    }
}
