<?php

namespace Aternos\Model\Driver\Elasticsearch;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\Driver\Features\SearchableInterface;
use Aternos\Model\ModelInterface;
use Aternos\Model\Search\Search;
use Aternos\Model\Search\SearchResult;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * Class Elasticsearch
 *
 * Inherit this class, overwrite the connect function
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver\Search
 */
class Elasticsearch extends Driver implements CRUDAbleInterface, SearchableInterface
{
    public const ID = "elasticsearch";
    protected string $id = self::ID;

    /**
     * @var Client|null
     */
    protected ?Client $client = null;

    /**
     * Connect to the elasticsearch cluster
     */
    protected function connect()
    {
        if (!$this->client) {
            $this->client = ClientBuilder::create()->build();
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
     * @param ModelInterface $model
     * @return bool
     */
    public function get(ModelInterface $model): bool
    {
        $params = [
            'index' => $model::getName(),
            'id' => $model->getId()
        ];

        $this->connect();
        try {
            $response = $this->client->getSource($params);
        } catch (Missing404Exception $e) {
            return false;
        }
        if (!is_array($response)) {
            return false;
        }

        foreach ($response as $key => $value) {
            $model->{$key} = $value;
        }
        return true;
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
            foreach ($resultDocument["_source"] as $key => $value) {
                $model->{$key} = $value;
            }
            $result->add($model);
        }
        return $result;
    }
}