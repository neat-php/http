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
    const HEADER = 'Content-Disposition';

    const INLINE = 'inline';
    const ATTACHMENT = 'attachment';

    /** @var string */
    private $disposition;

    /** @var string|null */
    private $filename;

    /** @var string|null */
    private $fieldname;

    /**
     * ContentDisposition constructor
     *
     * @param string      $disposition
     * @param string|null $filename
     * @param string|null $fieldname
     */
    public function __construct(string $disposition, string $filename = null, string $fieldname = null)
    {
        $this->disposition = $disposition;
        $this->filename    = $filename;
        $this->fieldname   = $fieldname;
    }

    /**
     * @return string
     */
    public function disposition(): string
    {
        return $this->disposition;
    }

    /**
     * @return string|null
     */
    public function filename()
    {
        return $this->filename;
    }

    /**
     * @return string|null
     */
    public function fieldname()
    {
        return $this->fieldname;
    }

    /**
     * @return string
     * @deprecated Use the disposition method instead
     */
    public function getValue(): string
    {
        return $this->disposition;
    }

    /**
     * @return string|null
     * @deprecated Use the filename method instead
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string|null
     * @deprecated Use the fieldname method instead
     */
    public function getName()
    {
        return $this->fieldname;
    }

    /**
     * @param Message $message
     * @return Message
     */
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

    /**
     * @param Message $message
     * @return self|null
     */
    public static function read(Message $message)
    {
        $header = $message->header(self::HEADER);
        if (!$header) {
            return null;
        }

        $parts    = explode('; ', $header->value());
        $value    = array_shift($parts);
        $filename = null;
        $name     = null;
        foreach ($parts as $part) {
            if (stripos($part, 'filename') === 0) {
                list(, $filename) = explode('=', $part);
                continue;
            }
            if (stripos($part, 'name') === 0) {
                list(, $name) = explode('=', $part);
                continue;
            }
        }

        return new self(
            $value,
            null !== $filename ? trim($filename, "\"") : null,
            null !== $name ? trim($name, "\"") : null
        );
    }
}
