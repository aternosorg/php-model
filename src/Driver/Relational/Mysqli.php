<?php

namespace Aternos\Model\Driver\Relational;

use Aternos\Model\ModelInterface;
use mysql_xdevapi\Exception;

/**
 * Class Mysqli
 *
 * Inherit this class, overwrite the connect function
 * and register the new class in the driver factory
 * for other credentials or connect specifics
 *
 * @author Matthias Neid
 * @package Aternos\Model\Driver
 */
class Mysqli implements RelationalDriverInterface
{
    /**
     * @var \mysqli
     */
    protected $connection;

    /**
     * Connect to database
     */
    protected function connect()
    {
        if (!$this->connection || !@mysqli_ping($this->connection)) {
            $this->connection = mysqli_connect();
            if (!$this->connection) {
                throw new Exception("Could not connect to Mysqli database. Error: " . mysqli_error($this->connection));
            }

            mysqli_select_db($this->connection, "data");
        }
    }

    /**
     * Execute a mysql query
     *
     * @param string $query
     * @return bool|\mysqli_result
     */
    protected function query(string $query)
    {
        $this->connect();
        return mysqli_query($this->connection, $query);
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

        $modelValues = get_object_vars($model);
        $columns = [];
        $values = [];
        foreach ($modelValues as $key => $value) {
            $columns[] = "`" . $key . "`";
            if (is_numeric($value)) {
                $values[] = $value;
            } else {
                $values[] = "'" . $value . "'";
            }
        }

        $updates = [];
        foreach ($modelValues as $column => $modelValue) {
            $updates[] = $columns . "=" . $modelValue;
        }

        $query = "INSERT INTO " . $table . " (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ") ON DUPLICATE KEY UPDATE " . implode(",", $updates);
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
        $result = $this->query($query);
        if (!$result) {
            return false;
        }

        $row = mysqli_fetch_assoc($result);
        foreach ($row as $key => $value) {
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