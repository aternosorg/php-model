<?php

namespace Aternos\Model\Driver\Elasticsearch;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\ModelInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * Class Elasticsearch
 *
 * Inherit this class, overwrite the connect function
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver\Search
 */
class Elasticsearch extends Driver implements CRUDAbleInterface
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