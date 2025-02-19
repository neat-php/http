<?php

namespace Neat\Http\Header;

use Neat\Http\Message;

/**
 * HTTP ContentDisposition header
 *
 * @url https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
 *
 * In a regular HTTP response, the Content-Disposition response header is a header indicating if the content is expected
 * to be displayed inline in the browser, that is, as a Web page or as part of a Web page, or as an attachment, that is
 * downloaded and saved locally.
 *
 * In a multipart/form-data body, the HTTP Content-Disposition general header is a header that can be used on the
 * subpart of a multipart body to give information about the field it applies to. The subpart is delimited by the
 * boundary defined in the Content-Type header. Used on the body itself, Content-Disposition has no effect.
 *
 * The Content-Disposition header is defined in the larger context of MIME messages for e-mail, but only a subset of the
 * possible parameters apply to HTTP forms and POST requests. Only the value form-data, as well as the optional
 * directive name and filename, can be used in the HTTP context.
 *
 * Syntax
 *
 * As a response header for the main body
 * The first parameter in the HTTP context is either inline (default value, indicating it can be displayed inside the
 * Web page, or as the Web page) or attachment (indicating it should be downloaded; most browsers presenting a 'Save as'
 * dialog, prefilled with the value of the filename parameters if present).
 *
 * Content-Disposition: inline
 * Content-Disposition: attachment
 * Content-Disposition: attachment; filename="filename.jpg"
 *
 * As a header for a multipart bodySection
 * The first parameter in the HTTP context is always form-data. Additional parameters are case-insensitive and have
 * arguments that use quoted-string syntax after the '=' sign. Multiple parameters are separated by a semi-colon (';').
 *
 * Content-Disposition: form-data
 * Content-Disposition: form-data; name="fieldName"
 * Content-Disposition: form-data; name="fieldName"; filename="filename.jpg"
 */
class ContentDisposition implements Header
{
    public const HEADER = 'Content-Disposition';

    public const INLINE = 'inline';
    public const ATTACHMENT = 'attachment';

    private string $disposition;
    private ?string $filename;
    private ?string $fieldname;

    public function __construct(string $disposition, ?string $filename = null, ?string $fieldname = null)
    {
        $this->disposition = $disposition;
        $this->filename = $filename;
        $this->fieldname = $fieldname;
    }

    public function disposition(): string
    {
        return $this->disposition;
    }

    public function filename(): ?string
    {
        return $this->filename;
    }

    public function fieldname(): ?string
    {
        return $this->fieldname;
    }

    public function write(Message $message): Message
    {
        $header = "$this->disposition";
        if ($this->filename) {
            $header .= "; filename=\"$this->filename\"";
        }
        if ($this->fieldname) {
            $header .= "; name=\"$this->fieldname\"";
        }

        return $message->withHeader(self::HEADER, $header);
    }

    public static function read(Message $message): ?self
    {
        $header = $message->header(self::HEADER);
        if (!$header) {
            return null;
        }

        $parts = explode('; ', $header->value());
        $value = array_shift($parts);
        $filename = null;
        $name = null;
        foreach ($parts as $part) {
            if (stripos($part, 'filename') === 0) {
                [, $filename] = explode('=', $part);
                continue;
            }
            if (stripos($part, 'name') === 0) {
                [, $name] = explode('=', $part);
                continue;
            }
        }

        return new self(
            $value,
            null !== $filename ? trim($filename, "\"") : null,
            null !== $name ? trim($name, "\"") : null,
        );
    }
}
