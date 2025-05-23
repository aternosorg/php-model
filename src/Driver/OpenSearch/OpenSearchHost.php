<?php

namespace Aternos\Model\Driver\OpenSearch;

use Aternos\Model\Driver\OpenSearch\Authentication\OpenSearchAuthenticationInterface;
use Aternos\Model\Driver\OpenSearch\Exception\HttpErrorResponseException;
use Aternos\Model\Driver\OpenSearch\Exception\HttpTransportException;
use Aternos\Model\Driver\OpenSearch\Exception\SerializeException;
use Exception;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use stdClass;

class OpenSearchHost
{
    /**
     * @param string $baseUri
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param OpenSearchAuthenticationInterface|null $authentication
     */
    public function __construct(
        protected string                             $baseUri,
        protected ClientInterface                    $client,
        protected RequestFactoryInterface            $requestFactory,
        protected StreamFactoryInterface             $streamFactory,
        protected ?OpenSearchAuthenticationInterface $authentication = null,
    )
    {
    }

    /**
     * @param string $method
     * @param string $uri
     * @param mixed|null $body
     * @return stdClass
     * @throws HttpErrorResponseException
     * @throws HttpTransportException
     * @throws SerializeException
     */
    public function request(string $method, string $uri, mixed $body = null): stdClass
    {
        $request = $this->requestFactory->createRequest($method, $this->baseUri . $uri);
        if ($body !== null) {
            $request = $request
                ->withHeader("Content-Type", "application/json")
                ->withBody($this->streamFactory->createStream($this->serialize($body)));
        }

        if ($this->authentication !== null) {
            $request = $this->authentication->applyTo($request);
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new HttpTransportException("OpenSearch request could not be sent", previous: $e);
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode > 299) {
            $parsed = null;
            try {
                $parsed = $this->parseResponse($response);
            } catch (Exception) {}
            throw new HttpErrorResponseException($parsed, "OpenSearch returned status code " . $statusCode, $statusCode);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @return stdClass
     * @throws HttpTransportException
     * @throws SerializeException
     */
    protected function parseResponse(ResponseInterface $response): stdClass
    {
        $contentType = $response->getHeaderLine('Content-Type');
        if (!str_contains(strtolower($contentType), 'application/json')) {
            throw new HttpTransportException("OpenSearch response is not a JSON response");
        }

        try {
            $responseBody = $response->getBody()->getContents();
        } catch (RuntimeException $e) {
            throw new HttpTransportException("OpenSearch response could not be read", previous: $e);
        }

        return $this->deserialize($responseBody);
    }

    /**
     * @param mixed $data
     * @return string
     * @throws SerializeException
     */
    protected function serialize(mixed $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        } catch (JsonException $e) {
            throw new SerializeException("Could not serialize OpenSearch data", previous: $e);
        }
    }

    /**
     * @param string $data
     * @return stdClass
     * @throws SerializeException
     */
    protected function deserialize(string $data): stdClass
    {
        try {
            $data = json_decode($data, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new SerializeException("Could not deserialize OpenSearch data", previous: $e);
        }

        if (!is_object($data)) {
            throw new SerializeException("Data must be an object");
        }

        return $data;
    }
}
