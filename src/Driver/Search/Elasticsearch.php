<?php

namespace Aternos\Model\Driver\Search;

use Aternos\Model\ModelInterface;

/**
 * Class Elasticsearch
 *
 * Inherit this class, overwrite the connect function
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver\Search
 */
class Elasticsearch implements SearchDriverInterface
{
    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * Connect to the elasticsearch cluster
     */
    protected function connect()
    {
        if (!$this->client) {
            $this->client = \Elasticsearch\ClientBuilder::create()->build();
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
            "type" => "_doc",
            "id" => $model->getId(),
            "body" => get_object_vars($model)
        ];

        $this->connect();
        $this->client->index($params);
        return true;
    }

    /**
     * Could be implemented, but should not be used
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function get(ModelInterface $model): bool
    {
        return false;
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
            "type" => "_doc",
            "id" => $model->getId()
        ];

        $this->client->delete($params);
        return true;
    }
}