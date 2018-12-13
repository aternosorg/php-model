<?php

namespace Aternos\Model\Driver\Cache;

use Aternos\Model\ModelInterface;

/**
 * Class Redis
 *
 * Inherit this class, overwrite the connect function
 * and/or the protected connection specific properties
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver\Cache
 */
class Redis implements CacheDriverInterface
{
    /**
     * Host address
     *
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * Host port
     *
     * @var int
     */
    protected $port = 6379;

    /**
     * Socket path
     *
     * If this is set, host and port are ignored
     *
     * @var bool
     */
    protected $socket = false;

    /**
     * @var \Redis
     */
    protected $connection;

    /**
     * Connect to redis
     */
    protected function connect()
    {
        if (!$this->connection) {
            $this->connection = new \Redis();
            if (!$this->socket) {
                $this->connection->connect($this->host, $this->port);
            } else {
                $this->connection->connect($this->socket);
            }
        }
    }

    /**
     * Generate a cache key string from a model
     *
     * @param ModelInterface $model
     * @return string
     */
    protected function generateCacheKey(ModelInterface $model): string
    {
        return "ATERNOS_MODEL::" . $model::getName() . "::" . $model->getId();
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function save(ModelInterface $model): bool
    {
        if (!$model->getCacheTime()) {
            return false;
        }

        $this->connect();
        return $this->connection->set($this->generateCacheKey($model), json_encode($model), $model->getCacheTime());
    }

    /**
     * Get the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function get(ModelInterface $model): bool
    {
        if (!$model->getCacheTime()) {
            return false;
        }

        $this->connect();
        $rawData = $this->connection->get($this->generateCacheKey($model));

        if (!$rawData) {
            return false;
        }

        $data = json_decode($rawData, true);
        foreach ($data as $key => $value) {
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
        if (!$model->getCacheTime()) {
            return false;
        }

        $this->connect();
        return $this->connection->delete($this->generateCacheKey($model));
    }
}