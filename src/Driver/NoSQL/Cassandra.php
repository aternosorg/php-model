<?php

namespace Aternos\Model\Driver\NoSQL;

use Aternos\Model\ModelInterface;

/**
 * Class Cassandra
 *
 * Inherit this class, overwrite the connect function
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @author Matthias Neid
 * @package Aternos\Model\Driver
 */
class Cassandra implements NoSQLDriverInterface
{
    /**
     * @var \Cassandra\Session
     */
    protected $connection;

    /**
     * Connect to cassandra database
     */
    protected function connect()
    {
        if (!$this->connection) {
            $cluster = \Cassandra::cluster()->build();
            $this->connection = $cluster->connect("data");
        }
    }

    /**
     * Execute a cassandra query
     *
     * @param $query
     * @return \Cassandra\Rows
     * @throws \Cassandra\Exception
     */
    protected function query(string $query)
    {
        $this->connect();

        $statement = new \Cassandra\SimpleStatement($query);
        $result = $this->connection->execute($statement);

        return $result;
    }

    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function save(ModelInterface $model): bool
    {
        $table = $model::getName();

        $data = json_encode($model);
        $data = str_replace("'", "''", $data);

        $query = "INSERT INTO " . $table . " JSON '" . $data . "';";
        $this->query($query);

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
        $table = $model::getName();

        $query = "SELECT * FROM " . $table . " WHERE " . $model->getIdField() . " = '" . $model->getId() . "'";
        $rows = $this->query($query);
        if ($rows->count() === 0) {
            return false;
        }

        $current = $rows->current();
        foreach ($current as $key => $value) {
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
        $table = $model::getName();

        $query = "DELETE FROM " . $table . " WHERE " . $model->getIdField() . " = '" . $model->getId() . "'";
        $this->query($query);
        return true;
    }
}