<?php

namespace Aternos\Model\Driver\OpenSearch;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\Driver\Features\SearchableInterface;
use Aternos\Model\Driver\OpenSearch\Authentication\OpenSearchAuthenticationInterface;
use Aternos\Model\Driver\OpenSearch\Exception\HttpErrorResponseException;
use Aternos\Model\Driver\OpenSearch\Exception\HttpTransportException;
use Aternos\Model\Driver\OpenSearch\Exception\OpenSearchException;
use Aternos\Model\Driver\OpenSearch\Exception\SerializeException;
use Aternos\Model\ModelInterface;
use Aternos\Model\Search\CountRelation;
use Aternos\Model\Search\Search;
use Aternos\Model\Search\SearchResult;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;

/**
 * Class Elasticsearch
 *
 * Inherit this class, overwrite the connect function
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver\Search
 */
class OpenSearch extends Driver implements CRUDAbleInterface, SearchableInterface
{
    public const ID = "opensearch";
    protected string $id = self::ID;

    /**
     * @var OpenSearchHost[]
     */
    protected array $hosts;

    protected int $maxRetries = 3;

    /**
     * @param string[] $hosts
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param OpenSearchAuthenticationInterface|null $authentication
     */
    public function __construct(
        array $hosts,
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected ?OpenSearchAuthenticationInterface $authentication = null,
    )
    {
        $this->hosts = [];
        foreach ($hosts as $host) {
            $this->hosts[] = new OpenSearchHost(
                $host,
                $this->client,
                $this->requestFactory,
                $this->streamFactory,
                $this->authentication
            );
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param mixed|null $body
     * @return stdClass
     * @throws HttpErrorResponseException If the response status code is not 2xx
     * @throws HttpTransportException If an error happens while the http client processes the request
     * @throws SerializeException If an error happens during (de-)serialization
     */
    protected function request(string $method, string $uri, mixed $body = null): stdClass
    {
        $offset = array_rand($this->hosts);
        $lastError = null;
        for ($i = 0; $i < $this->maxRetries; $i++) {
            $host = $this->hosts[$offset + $i % count($this->hosts)];
            try {
                return $host->request($method, $uri, $body);
            } catch (OpenSearchException $e) {
                $lastError = $e;
                if ($e instanceof HttpTransportException) {
                    continue;
                }
                if ($e instanceof HttpErrorResponseException && $e->getCode() >= 500 || in_array($e->getCode(), [404, 408])) {
                    continue;
                }
                throw $e;
            }
        }
        /** @var OpenSearchException $lastError */
        throw $lastError;
    }

    /**
     * @param string ...$path
     * @return string
     */
    protected function buildUrl(string ...$path): string
    {
        return "/" . implode("/", array_map(rawurlencode(...), $path));
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws HttpErrorResponseException If the response status code is not 2xx
     * @throws HttpTransportException If an error happens while the http client processes the request
     * @throws SerializeException If an error happens during (de-)serialization
     */
    public function save(ModelInterface $model): bool
    {
        $this->request(
            "PUT",
            $this->buildUrl($model::getName(), "_doc", $model->getId()),
            get_object_vars($model)
        );
        return true;
    }

    /**
     * Get the model
     *
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @param ModelInterface|null $model
     * @return ModelInterface|null
     * @throws HttpErrorResponseException If the response status code is not 2xx
     * @throws HttpTransportException If an error happens while the http client processes the request
     * @throws SerializeException If an error happens during (de-)serialization
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        try {
            $response = $this->request(
                "GET",
                $this->buildUrl($modelClass::getName(), "_doc", $id)
            );
        } catch (HttpErrorResponseException $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }

        $data = $this->getModelData($response, $modelClass);

        if ($model) {
            return $model->applyData($data);
        }
        return $modelClass::getModelFromData($data);
    }

    /**
     * Delete the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws HttpErrorResponseException If the response status code is not 2xx
     * @throws HttpTransportException If an error happens while the http client processes the request
     * @throws SerializeException If an error happens during (de-)serialization
     */
    public function delete(ModelInterface $model): bool
    {
        try {
            $this->request(
                "DELETE",
                $this->buildUrl($model::getName(), "_doc", $model->getId())
            );
        } catch (HttpErrorResponseException $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            throw $e;
        }

        return true;
    }

    /**
     * @param string $name
     * @return CountRelation|null
     */
    protected function getHitCountRelation(string $name): ?CountRelation
    {
        return match ($name) {
            "eq" => CountRelation::EQUALS,
            "gte" => CountRelation::GREATER_THAN_OR_EQUALS,
        };
    }

    /**
     * @param Search $search
     * @return SearchResult
     * @throws HttpErrorResponseException If the response status code is not 2xx
     * @throws HttpTransportException If an error happens while the http client processes the request
     * @throws SerializeException If an error happens during (de-)serialization
     */
    public function search(Search $search): SearchResult
    {
        /** @var class-string<ModelInterface> $modelClassName */
        $modelClassName = $search->getModelClassName();

        $response = $this->request(
            "GET",
            $this->buildUrl($modelClassName::getName(), "_search"),
            $search->getSearchQuery()
        );
        if (!isset($response->hits) || !is_object($response->hits) || !isset($response->hits->hits) || !is_array($response->hits->hits)) {
            throw new SerializeException("Received invalid search response from OpenSearch");
        }

        $result = new SearchResult();
        if (isset($response->took) && is_int($response->took)) {
            $result->setSearchTime($response->took);
        }

        if (isset($response->hits->total) && is_object($response->hits->total)) {
            if (isset($response->hits->total->value) && is_int($response->hits->total->value)) {
                $result->setTotalCount($response->hits->total->value);
            }
            if (isset($response->hits->total->relation) && is_string($response->hits->total->relation)) {
                $result->setTotalCountRelation($this->getHitCountRelation($response->hits->total->relation));
            }
        }

        foreach ($response->hits->hits as $resultDocument) {
            /** @var ModelInterface $model */
            $model = new $modelClassName();
            $model->applyData($this->getModelData($resultDocument, $modelClassName));
            $result->add($model);
        }
        return $result;
    }

    /**
     * @param stdClass $response
     * @param class-string<ModelInterface> $modelClass $modelClass
     * @return array
     */
    public function getModelData(stdClass $response, string $modelClass): array
    {
        if (!isset($response->_id) || !is_string($response->_id)) {
            throw new SerializeException("Received invalid document _id from OpenSearch");
        }

        if (isset($response->_source) && is_object($response->_source)) {
            $data = get_object_vars($response->_source);
        } else {
            $data = [];
        }

        $data[$modelClass::getIdField()] = $response->_id;
        return $data;
    }
}
