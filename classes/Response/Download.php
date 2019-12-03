<?php declare(strict_types=1);

namespace Neat\Http\Response;

use Neat\Http\Header\ContentDisposition;
use Neat\Http\Header\ContentType;
use Neat\Http\Header\Header;
use Neat\Http\Response;
use Psr\Http\Message\StreamInterface;

class Download
{
    /** @var string */
    private $body;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $name;

    /** @var bool */
    private $attachment;

    /**
     * Download constructor
     *
     * @param StreamInterface $body
     * @param string          $name
     * @param string          $mimeType
     * @param bool            $attachment
     */
    public function __construct(StreamInterface $body, string $name, string $mimeType, bool $attachment = true)
    {
        $this->body       = $body;
        $this->mimeType   = $mimeType;
        $this->name       = $name;
        $this->attachment = $attachment;
    }

    /**
     * @param Response $message
     * @return Response
     */
    public function write(Response $message): Response
    {
        foreach ($this->headers() as $header) {
            $message = $header->write($message);
        }

        return $message->withBody($this->body);
    }

    /**
     * @return Header[]
     */
    private function headers(): array
    {
        return [
            new ContentType($this->mimeType),
            new ContentDisposition(
                $this->attachment ? ContentDisposition::ATTACHMENT : ContentDisposition::INLINE,
                $this->name
            ),
        ];
    }
}
