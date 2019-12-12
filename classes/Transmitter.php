<?php

namespace Neat\Http;

use Neat\Http\Response\Redirect;

/**
 * Transmitter interface
 *
 * @deprecated Use Neat\Http\Server\Responder or Neat\Http\Server\Server instead.
 */
interface Transmitter
{
    /**
     * @param string $html
     * @return Response
     */
    public function html(string $html): Response;

    /**
     * @param array|object $body
     * @return Response
     */
    public function json($body): Response;

    /**
     * @return Redirect
     */
    public function redirect(): Redirect;

    /**
     * @param resource $resource
     * @param string   $name
     * @param string   $mimeType
     * @param bool     $attachment
     * @return Response
     */
    public function download($resource, string $name, string $mimeType, bool $attachment = true): Response;

    /**
     * @return Response
     */
    public function response(): Response;

    /**
     * @param Response $response
     * @return void
     */
    public function send(Response $response);
}
