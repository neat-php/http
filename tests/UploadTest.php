<?php

namespace Neat\Test\Http;

use Neat\Http\Upload;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UploadTest extends TestCase
{
    /**
     * Test defaults
     */
    public function testDefaults()
    {
        $file = new Upload(__DIR__ . '/test.txt');

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
        $file = new Upload(__DIR__ . '/test.txt', 'filename.txt', 'plain/text', UPLOAD_ERR_PARTIAL);

        $this->assertSame('filename.txt', $file->clientName());
        $this->assertSame('plain/text', $file->clientType());
        $this->assertSame(UPLOAD_ERR_PARTIAL, $file->error());
        $this->assertFalse($file->ok());
    }

    /**
     * Test invalid file upload
     */
    public function testInvalid()
    {
        $file = new Upload(__DIR__ . '/invalid.txt');

        $this->assertNull($file->size());
        $this->assertSame(UPLOAD_ERR_NO_FILE, $file->error());
        $this->assertFalse($file->ok());
    }

    /**
     * Test moving the uploaded file
     */
    public function testMove()
    {
        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::once())
            ->method('__invoke')
            ->with(__DIR__ . '/test.txt', __DIR__ . '/destination.txt')
            ->willReturn(true);

        $file = new Upload(__DIR__ . '/test.txt', 'test.txt', 'text/plain', UPLOAD_ERR_OK, $move);

        $this->assertFalse($file->moved());
        $file->moveTo(__DIR__ . '/destination.txt');
        $this->assertTrue($file->moved());
    }

    /**
     * Test moving the uploaded file twice
     */
    public function testMoveTwice()
    {
        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::once())
            ->method('__invoke')
            ->with(__DIR__ . '/test.txt', __DIR__ . '/destination1.txt')
            ->willReturn(true);

        $file = new Upload(__DIR__ . '/test.txt', 'test.txt', 'text/plain', UPLOAD_ERR_OK, $move);
        $file->moveTo(__DIR__ . '/destination1.txt');

        $this->expectExceptionObject(new RuntimeException('Uploaded file already moved'));
        $file->moveTo(__DIR__ . '/destination2.txt');
    }

    /**
     * Test moving an invalid file upload
     */
    public function testMoveInvalid()
    {
        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::never())
            ->method('__invoke');

        $file = new Upload(__DIR__ . '/invalid.txt', 'test.txt', 'text/plain', UPLOAD_ERR_NO_FILE, $move);

        $this->assertFalse($file->moved());

        $this->expectExceptionObject(new RuntimeException('Cannot move invalid file upload'));
        $file->moveTo(__DIR__ . '/destination.txt');
    }

    /**
     * Test uploaded file moving failure
     */
    public function testMoveFailed()
    {
        $move = $this->createPartialMock(\stdClass::class, ['__invoke']);
        $move
            ->expects(self::once())
            ->method('__invoke')
            ->with(__DIR__ . '/test.txt', __DIR__ . '/destination.txt')
            ->willReturn(false);

        $file = new Upload(__DIR__ . '/test.txt', 'test.txt', 'text/plain', UPLOAD_ERR_OK, $move);
        $this->assertTrue($file->ok());

        $this->expectExceptionObject(new RuntimeException('Failed moving uploaded file'));
        $file->moveTo(__DIR__ . '/destination.txt');
    }

    /**
     * Test capturing uploaded files from an empty set
     */
    public function testCaptureEmpty()
    {
        $this->assertSame([], Upload::capture([]));
        $this->assertSame([], Upload::capture(['empty' => []]));
    }

    /**
     * Test capturing a simple uploaded files
     */
    public function testCaptureSimple()
    {
        $captured = Upload::capture(
            [
                'avatar' => [
                    'tmp_name' => __DIR__ . '/test.txt',
                    'name'     => 'my-avatar.png',
                    'size'     => 90996,
                    'type'     => 'image/png',
                    'error'    => 0,
                ],
            ]
        );

        $this->assertEquals(
            [
                'avatar' => new Upload(__DIR__ . '/test.txt', 'my-avatar.png', 'image/png', 0),
            ],
            $captured
        );
    }

    /**
     * Test capturing uploaded files from a multi dimensional files array
     */
    public function testCaptureMultiDimensional()
    {
        $captured = Upload::capture(
            [
                'my-form' => [
                    'details' => [
                        'avatar' => [
                            'tmp_name' => __DIR__ . '/test.txt',
                            'name' => 'my-avatar.png',
                            'size' => 90996,
                            'type' => 'image/png',
                            'error' => 0,
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals(
            [
                'my-form' => [
                    'details' => [
                        'avatar' => new Upload(__DIR__ . '/test.txt', 'my-avatar.png', 'image/png'),
                    ],
                ],
            ],
            $captured
        );
    }

    /**
     * Test capturing multiple uploaded files from a non-normalized files array
     */
    public function testCaptureNormalized()
    {
        $captured = Upload::capture(
            [
                'my-form' => [
                    'details' => [
                        'avatars' => [
                            'tmp_name' => [
                                0 => __DIR__ . '/test.txt',
                                1 => __DIR__ . '/test.txt',
                            ],
                            'name' => [
                                0 => 'test1.txt',
                                1 => 'test2.txt',
                            ],
                            'size' => [
                                0 => 123,
                                1 => 123,
                            ],
                            'type' => [
                                0 => 'text/plain',
                                1 => 'text/plain',
                            ],
                            'error' => [
                                0 => 0,
                                1 => 0,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals(
            [
                'my-form' => [
                    'details' => [
                        'avatars' => [
                            0 => new Upload(__DIR__ . '/test.txt', 'test1.txt', 'text/plain'),
                            1 => new Upload(__DIR__ . '/test.txt', 'test2.txt', 'text/plain'),
                        ],
                    ],
                ],
            ],
            $captured
        );
    }

    /**
     * Test capturing uploaded files from an invalid files array
     */
    public function testCaptureInvalid()
    {
        $this->assertNull(Upload::capture(false));
        $this->assertSame([], Upload::capture(['avatar' => 1]));
    }
}
