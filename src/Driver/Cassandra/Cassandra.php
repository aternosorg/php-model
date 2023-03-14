<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Cassandra;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\Driver\Features\QueryableInterface;
use Aternos\Model\ModelInterface;
use Aternos\Model\Query\Generator\SQL;
use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;
use Cassandra\Exception;
use Cassandra\Rows;
use Cassandra\Session;
use Cassandra\SimpleStatement;

/**
 * Class Cassandra
 *
 * Inherit this class, overwrite the connect function
 * and/or the protected connection specific properties
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver
 */
class Cassandra extends Driver implements CRUDAbleInterface, QueryableInterface
{
    public const ID = "cassandra";
    protected string $id = self::ID;

    /**
     * Host address (localhost by default)
     *
     * Can actually be one or multiple comma separated hosts (contact points)
     *
     * @var null|string
     */
    protected ?string $host = null;

    /**
     * Host port
     *
     * @var int|null
     */
    protected ?int $port = null;

    /**
     * Authentication username
     *
     * @var null|string
     */
    protected ?string $user = null;

    /**
     * Authentication password
     *
     * @var null|string
     */
    protected ?string $password = null;

    /**
     * Keyspace name
     *
     * @var string
     */
    protected string $keyspace = "data";

    /**
     * @var Session|null
     */
    protected ?Session $connection = null;

    /**
     * Cassandra constructor.
     *
     * @param string|null $host
     * @param int|null $port
     * @param string|null $user
     * @param string|null $password
     * @param string $keyspace
     */
    public function __construct(?string $host = null, ?int $port = null, ?string $user = null, ?string $password = null, string $keyspace = "data")
    {
        $this->host = $host ?? $this->host;
        $this->port = $port ?? $this->port;
        $this->user = $user ?? $this->user;
        $this->password = $password ?? $this->password;
        $this->keyspace = $keyspace ?? $this->keyspace;
    }

    /**
     * Connect to cassandra database
     */
    protected function connect(): void
    {
        if ($this->connection) {
            return;
        }

        $builder = \Cassandra::cluster();

        if ($this->host) {
            $builder->withContactPoints($this->host);
        }

        if ($this->port) {
            $builder->withPort($this->port);
        }

        if ($this->user && $this->password) {
            $builder->withCredentials($this->user, $this->password);
        }

        $cluster = $builder->build();
        $this->connection = $cluster->connect($this->keyspace);
    }

    /**
     * Execute a cassandra query
     *
     * @param string $query
     * @return Rows
     * @throws Exception
     */
    protected function rawQuery(string $query): Rows
    {
        $this->connect();

        $statement = new SimpleStatement($query);
        return $this->connection->execute($statement, null);
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws Exception
     */
    public function save(ModelInterface $model): bool
    {
        $table = $model::getName();

        $data = json_encode($model);
        $data = str_replace("'", "''", $data);

        $query = "INSERT INTO " . $table . " JSON '" . $data . "';";
        $this->rawQuery($query);

        return true;
    }

    /**
     * Get the model
     *
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @param ModelInterface|null $model
     * @return ModelInterface|null
     * @throws Exception
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        $table = $modelClass::getName();

        $id = str_replace("'", "''", $id);
        $query = "SELECT * FROM " . $table . " WHERE " . $modelClass::getIdField() . " = '" . $id . "'";
        $rows = $this->rawQuery($query);
        if ($rows->count() === 0) {
            return null;
        }

        $current = $rows->current();
        if ($model) {
            return $model->applyData($current);
        }
        return $modelClass::getModelFromData($current);
    }

    /**
     * Delete the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws Exception
     */
    public function delete(ModelInterface $model): bool
    {
        $table = $model::getName();

        $id = str_replace("'", "''", $model->getId());
        $query = "DELETE FROM " . $table . " WHERE " . $model->getIdField() . " = '" . $id . "'";
        $this->rawQuery($query);
        return true;
    }

    /**
     * Execute a SELECT, UPDATE or DELETE query
     *
     * @param Query $query
     * @return QueryResult
     * @throws Exception
     */
    public function query(Query $query): QueryResult
    {
        $this->connect();

        $generator = new SQL(function ($value) {
            return str_replace("'", "''", $value);
        });

        $generator->columnEnclosure = "";
        $generator->tableEnclosure = "";

        $queryString = $generator->generate($query);

        $rawQueryResult = $this->rawQuery($queryString);

        $result = new QueryResult((bool)$rawQueryResult);
        $result->setQueryString($queryString);
        foreach ($rawQueryResult as $resultRow) {
            /** @var class-string<ModelInterface> $modelClass */
            $modelClass = $query->modelClassName;
            $model = $modelClass::getModelFromData($resultRow);
            $result->add($model);
        }

        return $result;
    }

    /**
     * @param string|null $host
     * @return Cassandra
     */
    public function setHost(?string $host): Cassandra
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param int|null $port
     * @return Cassandra
     */
    public function setPort(?int $port): Cassandra
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string|null $user
     * @return Cassandra
     */
    public function setUser(?string $user): Cassandra
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string|null $password
     * @return Cassandra
     */
    public function setPassword(?string $password): Cassandra
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $keyspace
     * @return Cassandra
     */
    public function setKeyspace(string $keyspace): Cassandra
    {
        $this->keyspace = $keyspace;
        return $this;
    }
}