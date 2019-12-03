<?php declare(strict_types=1);

namespace Neat\Http\Response;

use Neat\Http\Request;
use Neat\Http\Response;

class Redirect
{
    /** @var Response */
    private $response;

    /**
     * Redirect constructor
     *
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @param string $location
     * @param bool   $permanent
     * @return Response
     */
    public function to(string $location, bool $permanent = false): Response
    {
        return $this->response
            ->withStatus($permanent ? 301 : 302)
            ->withHeader('Location', $location);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function back(Request $request): Response
    {
        $url = $request->header('Referer');

        return $this->to($url->value());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function refresh(Request $request): Response
    {
        return $this->to($request->url()->get());
    }
}
