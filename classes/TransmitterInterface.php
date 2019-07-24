<?php

namespace Neat\Http;

use Neat\Http\Response\Redirect;

interface TransmitterInterface
{
    public function html(string $html): Response;

    public function json($body): Response;

    public function redirect(): Redirect;

    public function download($resource, string $name, string $mimeType, bool $attachment = true): Response;

    public function response(): Response;

    public function send(Response $response);
}
