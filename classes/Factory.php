<?php

namespace Neat\Http;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
//use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Factory
{
    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var ServerRequestFactoryInterface */
    private $serverRequestFactory;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /** @var UploadedFileFactoryInterface */
    private $uploadedFileFactory;

    /** @var UriFactoryInterface */
    private $uriFactory;

    /**
     * Factory constructor
     *
     * @param RequestFactoryInterface       $requestFactory
     * @param ServerRequestFactoryInterface $serverRequestFactory
     * @param ResponseFactoryInterface      $responseFactory
     * @param StreamFactoryInterface        $streamFactory
     * @param UploadedFileFactoryInterface  $uploadedFileFactory
     * @param UriFactoryInterface           $uriFactory
     */
    public function __construct(
        RequestFactoryInterface $requestFactory,
        ServerRequestFactoryInterface $serverRequestFactory,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        UriFactoryInterface $uriFactory
    ) {
        $this->requestFactory       = $requestFactory;
        $this->serverRequestFactory = $serverRequestFactory;
        $this->responseFactory      = $responseFactory;
        $this->streamFactory        = $streamFactory;
        $this->uploadedFileFactory  = $uploadedFileFactory;
        $this->uriFactory           = $uriFactory;
    }

    /**
     * Create request
     *
     * @param string $method
     * @param string $uri
     * @return Request
     */
    public function request(string $method, string $uri): Request
    {
        $uri = $this->uriFactory->createUri($uri);
        $psr = $this->serverRequestFactory->createServerRequest($method, $uri);

        return new Request($psr);
    }

//    /**
//     * Create server request
//     *
//     * @param string $method
//     * @param string $uri
//     * @return ServerRequest
//     */
//    public function serverRequest(string $method, string $uri): ServerRequest
//    {
//        $uri = $this->uriFactory->createUri($uri);
//        $psr = $this->requestFactory->createRequest($method, $uri);
//
//        return new ServerRequest($psr);
//    }

    /**
     * Create response
     *
     * @param int    $code
     * @param string $reason
     * @return Response
     */
    public function response(int $code, string $reason): Response
    {
        $psr = $this->responseFactory->createResponse($code, $reason);

        return new Response($psr);
    }

//    public function stream(string $content = ''): Stream
//    {
//        $psr = $this->streamFactory->createStream($content);
//
//        return new Stream($psr);
//    }
//
//    public function upload(): Upload
//    {
//        $psr = $this->uploadedFileFactory->createUploadedFile(
//            StreamInterface $stream,
//            int $size = null,
//            int $error = \UPLOAD_ERR_OK,
//            string $clientFilename = null,
//            string $clientMediaType = null
//        )
//
//        return new Upload($psr);
//    }

    public function url($url): Url
    {
        $psr = $this->uriFactory->createUri($url);

        return new Url($psr);
    }
}
