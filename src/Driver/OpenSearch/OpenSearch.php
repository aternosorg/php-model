<?php

namespace Aternos\Model\Driver\OpenSearch;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\Driver\Features\SearchableInterface;
use Aternos\Model\ModelInterface;
use Aternos\Model\Search\Search;
use Aternos\Model\Search\SearchResult;
use OpenSearch\Client;
use OpenSearch\EndpointFactoryInterface;
use OpenSearch\Exception\NotFoundHttpException;
use OpenSearch\Namespaces\NamespaceBuilderInterface;
use OpenSearch\TransportFactory;
use OpenSearch\TransportInterface;

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
     * @var Client|null
     */
    protected ?Client $client = null;

    /**
     * @param TransportInterface|null $transport
     * @param EndpointFactoryInterface|null $endpointFactory
     * @param NamespaceBuilderInterface[] $registeredNamespaces
     */
    public function __construct(
        protected ?TransportInterface $transport = null,
        protected ?EndpointFactoryInterface $endpointFactory = null,
        protected array $registeredNamespaces = [],
    )
    {
    }

    /**
     * Connect to the elasticsearch cluster
     */
    protected function connect(): void
    {
        if (!$this->client) {
            $this->client = new Client(
                $this->transport ?? (new TransportFactory())->create(),
                $this->endpointFactory,

            );
        }
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function save(ModelInterface $model): bool
    {
        $params = [
            "index" => $model::getName(),
            "id" => $model->getId(),
            "body" => get_object_vars($model)
        ];

        $this->connect();
        $this->client->index($params);
        return true;
    }

    /**
     * Get the model
     *
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @param ModelInterface|null $model
     * @return ModelInterface|null
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        $params = [
            'index' => $modelClass::getName(),
            $modelClass::getIdField() => $id
        ];

        $this->connect();
        try {
            $response = $this->client->getSource($params);
        } catch (NotFoundHttpException) {
            return null;
        }
        if (!is_array($response)) {
            return null;
        }

        if ($model) {
            return $model->applyData($response);
        }
        return $modelClass::getModelFromData($response);
    }

    /**
     * Delete the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function delete(ModelInterface $model): bool
    {
        $params = [
            "index" => $model::getName(),
            "id" => $model->getId()
        ];

        $this->client->delete($params);
        return true;
    }

    /**
     * @param Search $search
     * @return SearchResult
     */
    public function search(Search $search): SearchResult
    {
        /** @var class-string<ModelInterface> $modelClassName */
        $modelClassName = $search->getModelClassName();
        $params = [
            'index' => $modelClassName::getName(),
            'body' => $search->getSearchQuery()
        ];

        $this->connect();
        $response = $this->client->search($params);
        if (!is_array($response) || !isset($response["hits"]) || !is_array($response["hits"]) || !isset($response["hits"]["hits"]) || !is_array($response["hits"]["hits"])) {
            return new SearchResult(false);
        }

        $result = new SearchResult(true);
        foreach ($response["hits"]["hits"] as $resultDocument) {
            if (!isset($resultDocument["_source"]) || !is_array($resultDocument["_source"])) {
                continue;
            }

            /** @var ModelInterface $model */
            $model = new $modelClassName();
            $model->applyData($resultDocument["_source"]);
            $result->add($model);
        }
        return $result;
    }
}
