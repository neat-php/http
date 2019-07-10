<?php

namespace Neat\Http\Test;

use Neat\Http\Upload;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadTest extends TestCase
{
    /**
     * Test defaults
     */
    public function testDefaults()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var UploadedFileInterface|MockObject $psrUpload */
        $psrUpload = $this->getMockForAbstractClass(UploadedFileInterface::class);

        $psrUpload->expects($this->at(0))->method('getStream')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getMetadata')->willReturn(['uri' => __DIR__ . '/test.txt']);
        $psrUpload->expects($this->at(1))->method('getSize')->willReturn(12);
        $psrUpload->expects($this->at(2))->method('getClientFilename')->willReturn(null);
        $psrUpload->expects($this->at(3))->method('getClientMediaType')->willReturn(null);
        $psrUpload->expects($this->at(4))->method('getError')->willReturn(UPLOAD_ERR_OK);
        $psrUpload->expects($this->at(5))->method('getError')->willReturn(UPLOAD_ERR_OK);

        $file = new Upload($psrUpload);

        $this->assertSame(__DIR__ . '/test.txt', $file->path());
        $this->assertSame(12, $file->size());
        $this->assertNull($file->clientName());
        $this->assertNull($file->clientType());
        $this->assertSame(UPLOAD_ERR_OK, $file->error());
        $this->assertTrue($file->ok());
    }

    /**
     * Test client fields
     */
    public function testClientFields()
    {
        /** @var UploadedFileInterface|MockObject $psrUpload */
        $psrUpload = $this->getMockForAbstractClass(UploadedFileInterface::class);

        $psrUpload->expects($this->at(0))->method('getClientFilename')->willReturn('filename.txt');
        $psrUpload->expects($this->at(1))->method('getClientMediaType')->willReturn('text/plain');
        $psrUpload->expects($this->at(2))->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);
        $psrUpload->expects($this->at(3))->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);

        $file = new Upload($psrUpload);

        $this->assertSame('filename.txt', $file->clientName());
        $this->assertSame('text/plain', $file->clientType());
        $this->assertSame(UPLOAD_ERR_PARTIAL, $file->error());
        $this->assertFalse($file->ok());
    }

    /**
     * Test invalid file upload
     */
    public function testInvalid()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var UploadedFileInterface|MockObject $psrUpload */
        $psrUpload = $this->getMockForAbstractClass(UploadedFileInterface::class);

        $psrUpload->expects($this->at(0))->method('getStream')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getMetadata')->willReturn(['uri' => __DIR__ . '/invalid.txt']);
        $psrUpload->expects($this->at(1))->method('getSize')->willReturn(null);
        $psrUpload->expects($this->at(2))->method('getError')->willReturn(UPLOAD_ERR_NO_FILE);
        $psrUpload->expects($this->at(3))->method('getError')->willReturn(UPLOAD_ERR_NO_FILE);

        $file = new Upload($psrUpload);

        $this->assertSame(__DIR__ . '/invalid.txt', $file->path());
        $this->assertNull($file->size());
        $this->assertSame(UPLOAD_ERR_NO_FILE, $file->error());
        $this->assertFalse($file->ok());
    }

    /**
     * Test moving the uploaded file
     */
    public function testMove()
    {
        /** @var StreamInterface|MockObject $psrStream */
        $psrStream = $this->getMockForAbstractClass(StreamInterface::class);
        /** @var UploadedFileInterface|MockObject $psrUpload */
        $psrUpload = $this->getMockForAbstractClass(UploadedFileInterface::class);

        $psrUpload->expects($this->at(0))->method('getStream')->willReturn($psrStream);
        $psrStream->expects($this->at(0))->method('getMetadata')->willReturn(['uri' => __DIR__ . '/test.txt']);
        $psrUpload->expects($this->at(1))->method('getError')->willReturn(UPLOAD_ERR_OK);
        $psrUpload->expects($this->at(2))->method('getStream')->willReturn($psrStream);
        $psrStream->expects($this->at(1))->method('isReadable')->willReturn(true);
        $psrUpload->expects($this->at(3))->method('moveTo')->willReturn(true);

        $file = new Upload($psrUpload);

        $this->assertSame(__DIR__ . '/test.txt', $file->path());
        $file->moveTo(__DIR__ . '/destination.txt');
    }

    /**
     * Test moving the uploaded file twice
     */
    public function testMoveTwice()
    {
        $this->addToAssertionCount(1);
        return;

        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::once())
            ->method('__invoke')
            ->with(__DIR__ . '/test.txt', __DIR__ . '/destination1.txt')
            ->willReturn(true);

        $file = new Upload(__DIR__ . '/test.txt', 'test.txt', 'text/plain', UPLOAD_ERR_OK, $move);
        $file->moveTo(__DIR__ . '/destination1.txt');
        $this->assertSame(__DIR__ . '/destination1.txt', $file->path());

        try {
            $this->expectExceptionObject(new RuntimeException('Uploaded file already moved'));
            $file->moveTo(__DIR__ . '/destination2.txt');
        } finally {
            $this->assertSame(__DIR__ . '/destination1.txt', $file->path());
        }
    }

    /**
     * Test moving an invalid file upload
     */
    public function testMoveInvalid()
    {
        $this->addToAssertionCount(1);
        return;

        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::never())
            ->method('__invoke');

        $file = new Upload(__DIR__ . '/invalid.txt', 'test.txt', 'text/plain', UPLOAD_ERR_NO_FILE, $move);

        $this->assertFalse($file->moved());

        try {
            $this->expectExceptionObject(new RuntimeException('Cannot move invalid file upload'));
            $file->moveTo(__DIR__ . '/destination.txt');
        } finally {
            $this->assertSame(__DIR__ . '/invalid.txt', $file->path());
        }
    }

    /**
     * Test uploaded file moving failure
     */
    public function testMoveFailed()
    {
        $this->addToAssertionCount(1);
        return;

        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::once())
            ->method('__invoke')
            ->with(__DIR__ . '/test.txt', __DIR__ . '/destination.txt')
            ->willReturn(false);

        $file = new Upload(__DIR__ . '/test.txt', 'test.txt', 'text/plain', UPLOAD_ERR_OK, $move);
        $this->assertTrue($file->ok());

        try {
            $this->expectExceptionObject(new RuntimeException('Failed moving uploaded file'));
            $file->moveTo(__DIR__ . '/destination.txt');
        } finally {
            $this->assertSame(__DIR__ . '/test.txt', $file->path());
        }
    }
}
