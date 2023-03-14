<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Redis;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CacheableInterface;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\ModelInterface;
use RedisException;

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
class Redis extends Driver implements CRUDAbleInterface, CacheableInterface
{
    public const ID = "redis";
    protected string $id = self::ID;

    /**
     * Host address
     *
     * @var string
     */
    protected string $host = '127.0.0.1';

    /**
     * Host port
     *
     * @var int
     */
    protected int $port = 6379;

    /**
     * Socket path
     *
     * If this is set, host and port are ignored
     *
     * @var null|string
     */
    protected ?string $socket = null;

    /**
     * @var \Redis|null
     */
    protected ?\Redis $connection = null;

    /**
     * Redis constructor.
     * @param string|null $host
     * @param int|null $port
     * @param string|null $socket
     */
    public function __construct(?string $host = null, ?int $port = null, ?string $socket = null)
    {
        $this->host = $host ?? $this->host;
        $this->port = $port ?? $this->port;
        $this->socket = $socket ?? $this->socket;
    }

    /**
     * Connect to redis
     *
     * @throws RedisException
     */
    protected function connect(): void
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
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @return string
     */
    protected function generateCacheKey(string $modelClass, mixed $id): string
    {
        return "ATERNOS_MODEL::" . $modelClass::getName() . "::" . $id;
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws RedisException
     */
    public function save(ModelInterface $model): bool
    {
        if (!$model->getCacheTime()) {
            return true;
        }

        $this->connect();
        return $this->connection->set($this->generateCacheKey($model::class, $model->getId()), json_encode($model), $model->getCacheTime());
    }

    /**
     * Get the model
     *
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @param ModelInterface|null $model
     * @return ModelInterface|null
     * @throws RedisException
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        if (!$modelClass::getCacheTime()) {
            return null;
        }

        $this->connect();
        $rawData = $this->connection->get($this->generateCacheKey($modelClass, $id));

        if (!$rawData) {
            return null;
        }

        $data = json_decode($rawData, true);
        if (!is_array($data)) {
            return null;
        }

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
     * @throws RedisException
     */
    public function delete(ModelInterface $model): bool
    {
        if (!$model->getCacheTime()) {
            return true;
        }

        $this->connect();
        return $this->connection->del($this->generateCacheKey($model::class, $model->getId()));
    }

    /**
     * @param string $host
     * @return Redis
     */
    public function setHost(string $host): Redis
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param int $port
     * @return Redis
     */
    public function setPort(int $port): Redis
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string|null $socket
     * @return Redis
     */
    public function setSocket(?string $socket): Redis
    {
        $this->socket = $socket;
        return $this;
    }
}