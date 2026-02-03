<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Mysqli;

use Aternos\Model\{Driver\Driver,
    Driver\Features\CRUDAbleInterface,
    Driver\Features\CRUDQueryableInterface,
    ModelInterface,
    Query\DeleteQuery,
    Query\Generator\SQL,
    Query\Query,
    Query\QueryResult,
    Query\UpdateQuery
};
use mysqli_result;
use mysqli_sql_exception;

/**
 * Class Mysqli
 *
 * Inherit this class, overwrite the connect function
 * and/or the protected connection specific properties
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @package Aternos\Model\Driver
 */
class Mysqli extends Driver implements CRUDAbleInterface, CRUDQueryableInterface
{
    public const string ID = "mysqli";
    protected string $id = self::ID;

    /**
     * Host address
     *
     * @var string|null
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
     * @var string|null
     */
    protected ?string $username = null;

    /**
     * Authentication password
     *
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * Socket path or pipe
     *
     * @var string|null
     */
    protected ?string $socket = null;

    /**
     * Database name
     *
     * @var string
     */
    protected string $database = "data";

    /**
     * @var \mysqli|null
     */
    protected ?\mysqli $connection = null;

    protected int $connectionRetries = 1;

    /**
     * Mysqli constructor.
     *
     * @param string|null $host
     * @param int|null $port
     * @param string|null $username
     * @param string|null $password
     * @param string|null $socket
     * @param string|null $database
     */
    public function __construct(?string $host = null, ?int $port = null, ?string $username = null, ?string $password = null, ?string $socket = null, ?string $database = null)
    {
        $this->host = $host ?? $this->host;
        $this->port = $port ?? $this->port;
        $this->username = $username ?? $this->username;
        $this->password = $password ?? $this->password;
        $this->socket = $socket ?? $this->socket;
        $this->database = $database ?? $this->database;
    }

    /**
     * Connect to database
     * @throws MysqliConnectionException if connecting to the mysql database fails
     */
    protected function connect(): void
    {
        if ($this->connection) {
            return;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database, $this->port, $this->socket);
        } catch (mysqli_sql_exception $e) {
            throw MysqliConnectionException::fromException($e);
        }
    }

    protected function reconnect(): void
    {
        $this->connection = null;
        $this->connect();
    }

    /**
     * Execute a mysql query
     *
     * @param string $query
     * @return bool|mysqli_result
     * @throws MysqliConnectionException if connecting to the mysql database fails
     * @throws MysqliException if a mysql error occurs while executing the query
     */
    protected function rawQuery(string $query): mysqli_result|true
    {
        $this->connect();
        $retries = $this->connectionRetries;
        while (true) {
            try {
                return $this->connection->query($query);
            } catch (mysqli_sql_exception $e) {
                // no more retries left
                if ($retries <= 0) {
                    throw MysqliException::fromException($e);
                }

                // connection error, try to reconnect and retry
                if ($e->getCode() === 2006 || $e->getCode() === 2013) {
                    $this->reconnect();
                    $retries--;
                    continue;
                }

                // other error, throw exception
                throw MysqliException::fromException($e);
            }
        }
    }

    /**
     * Escape a string for use in a mysql query
     *
     * @param string $data
     * @return string
     */
    protected function escape(string $data): string
    {
        $this->connect();
        $retries = $this->connectionRetries;
        while (true) {
            try {
                return $this->connection->real_escape_string($data);
            } catch (mysqli_sql_exception $e) {
                // no more retries left
                if ($retries <= 0) {
                    throw MysqliException::fromException($e);
                }

                // connection error, try to reconnect and retry
                if ($e->getCode() === 2006 || $e->getCode() === 2013) {
                    $this->reconnect();
                    $retries--;
                    continue;
                }

                // other error, throw exception
                throw MysqliException::fromException($e);
            }
        }
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws MysqliConnectionException if connecting to the mysql database fails
     * @throws MysqliException if a mysql error occurs while executing the query
     */
    public function save(ModelInterface $model): bool
    {
        $this->connect();

        $table = $model::getName();

        $modelValues = get_object_vars($model);
        $columns = [];
        $values = [];
        foreach ($modelValues as $key => $value) {
            $columns[] = "`" . $key . "`";
            if (is_int($value) || is_float($value)) {
                $values[] = $value;
            } else if (is_null($value)) {
                $values[] = "NULL";
            } else {
                $values[] = "'" . $this->escape($value) . "'";
            }
        }

        $updates = [];
        foreach ($modelValues as $column => $modelValue) {
            if (is_int($modelValue) || is_float($modelValue)) {
                $updates[] = "`" . $column . "`=" . $modelValue;
            } else if (is_null($modelValue)) {
                $updates[] = "`" . $column . "`=NULL";
            } else {
                $updates[] = "`" . $column . "`='" . $this->escape($modelValue) . "'";
            }
        }

        $query = "INSERT INTO `" . $table . "` (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ") ON DUPLICATE KEY UPDATE " . implode(",", $updates);
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
     * @throws MysqliConnectionException if connecting to the mysql database fails
     * @throws MysqliException if a mysql error occurs while executing the query
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        $this->connect();
        $table = $modelClass::getName();

        $escapedId = $this->escape($id);
        $query = "SELECT * FROM `" . $table . "` WHERE `" . $modelClass::getIdField() . "` = '" . $escapedId . "'";
        $result = $this->rawQuery($query);
        if (!$result || $result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        if ($model) {
            return $model->applyData($row);
        }
        return $modelClass::getModelFromData($row);
    }

    /**
     * Delete the model
     *
     * @param ModelInterface $model
     * @return bool
     * @throws MysqliConnectionException if connecting to the mysql database fails
     * @throws MysqliException if a mysql error occurs while executing the query
     */
    public function delete(ModelInterface $model): bool
    {
        $this->connect();
        $table = $model::getName();

        $id = $this->escape($model->getId());
        $query = "DELETE FROM `" . $table . "` WHERE `" . $model->getIdField() . "` = '" . $id . "'";
        $this->rawQuery($query);

        return true;
    }

    /**
     * Execute a SELECT, UPDATE or DELETE query
     *
     * @param Query $query
     * @return QueryResult
     * @throws MysqliConnectionException if connecting to the mysql database fails
     * @throws MysqliException if a mysql error occurs while executing the query
     */
    public function query(Query $query): QueryResult
    {
        $this->connect();

        $generator = new SQL($this->escape(...));

        $queryString = $generator->generate($query);

        $rawQueryResult = $this->rawQuery($queryString);

        $result = new QueryResult();
        $result->setQueryString($queryString);
        if ($query instanceof UpdateQuery || $query instanceof DeleteQuery) {
            $result->setAffectedRows($this->connection->affected_rows);
            return $result;
        }

        if (is_bool($rawQueryResult)) {
            return $result;
        }

        while ($row = $rawQueryResult->fetch_assoc()) {
            /** @var class-string<ModelInterface> $modelClass */
            $modelClass = $query->modelClassName;
            $model = $modelClass::getModelFromData($row);
            if ($model) {
                $result->add($model);
            }
        }

        return $result;
    }

    /**
     * @param string|null $host
     * @return $this
     */
    public function setHost(?string $host): Mysqli
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param int|null $port
     * @return $this
     */
    public function setPort(?int $port): Mysqli
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string|null $username
     * @return $this
     */
    public function setUsername(?string $username): Mysqli
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param string|null $password
     * @return $this
     */
    public function setPassword(?string $password): Mysqli
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string|null $socket
     * @return $this
     */
    public function setSocket(?string $socket): Mysqli
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @param string $database
     * @return $this
     */
    public function setDatabase(string $database): Mysqli
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @param int $connectionRetries
     * @return $this
     */
    public function setConnectionRetries(int $connectionRetries): static
    {
        $this->connectionRetries = $connectionRetries;
        return $this;
    }
}
