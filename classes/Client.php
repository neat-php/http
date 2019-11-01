<?php

namespace Neat\Http;

use Neat\Http\Exception\ClientException;
use Neat\Http\Exception\NetworkException;
use Neat\Http\Exception\RequestException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Client
{
    /** @var ClientInterface */
    private $client;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var UriFactoryInterface */
    private $uriFactory;

    /**
     * Client constructor
     *
     * @param ClientInterface         $client
     * @param RequestFactoryInterface $requestFactory
     * @param UriFactoryInterface     $uriFactory
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory
    ) {
        $this->client         = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory     = $uriFactory;
    }

    /**
     * GET request
     *
     * @param Url|string $url
     * @return Response
     */
    public function get($url): Response
    {
        return $this->request('GET', $url);
    }

    /**
     * POST request
     *
     * @param Url|string $url
     * @return Response
     */
    public function post($url): Response
    {
        return $this->request('POST', $url);
    }

    /**
     * HEAD request
     *
     * @param Url|string $url
     * @return Response
     */
    public function head($url): Response
    {
        return $this->request('HEAD', $url);
    }

    /**
     * PUT request
     *
     * @param Url|string $url
     * @return Response
     */
    public function put($url): Response
    {
        return $this->request('PUT', $url);
    }

    /**
     * DELETE request
     *
     * @param Url|string $url
     * @return Response
     */
    public function delete($url): Response
    {
        return $this->request('DELETE', $url);
    }

    /**
     * OPTIONS request
     *
     * @param Url|string $url
     * @return Response
     */
    public function options($url): Response
    {
        return $this->request('OPTIONS', $url);
    }

    /**
     * PATCH request
     *
     * @param Url|string $url
     * @return Response
     */
    public function patch($url): Response
    {
        return $this->request('PATCH', $url);
    }

    /**
     * Create and send request
     *
     * @param string     $method
     * @param Url|string $url
     * @return Response
     * @throws ClientException
     */
    public function request($method, $url): Response
    {
        $uri     = $url instanceof Url ? $url->getUri() : $this->uriFactory->createUri($url);
        $request = new Request($this->requestFactory->createRequest($method, $uri));

        return $this->send($request);
    }

    /**
     * Send request
     *
     * @param Request $request
     * @return Response
     * @throws ClientException
     */
    public function send(Request $request): Response
    {
        try {
            $psrResponse = $this->client->sendRequest($request->psr());
        } catch (NetworkExceptionInterface $exception) {
            throw new NetworkException($exception);
        } catch (RequestExceptionInterface $exception) {
            throw new RequestException($exception);
        } catch (ClientExceptionInterface $exception) {
            throw new ClientException($exception);
        }

        return new Response($psrResponse);
    }
}
