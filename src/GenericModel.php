<?php

namespace Aternos\Model;

use Aternos\Model\Driver\{DriverFactory, QueryableDriverInterface};
use Aternos\Model\Query\{Limit, Query, QueryResult, SelectQuery, UpdateQuery, WhereCondition, WhereGroup};

/**
 * Class GenericModel
 *
 * Generic model using all drivers optionally, enable/disable them by
 * overwriting the protected static properties. Good start for small to
 * medium complexity.
 *
 * @package Aternos\Model
 */
abstract class GenericModel extends BaseModel
{
    /**
     * Should the model registry be used for this model
     *
     * @var bool
     */
    protected static $registry = true;

    /**
     * Is this model cacheable and if yes, for how long (seconds)
     *
     * @var bool|int
     */
    protected static $cache = false;

    /**
     * Should this model be saved in a nosql database
     *
     * @var bool
     */
    protected static $nosql = false;

    /**
     * Should this model be saved in a relational database
     *
     * @var bool
     */
    protected static $relational = true;

    /**
     * Is this model searchable and if yes, which fields
     *
     * @var bool|array
     */
    protected static $search = false;

    /**
     * Get the driver factory for the current model
     *
     * @return Driver\DriverFactory
     */
    protected static function getDriverFactory()
    {
        $driverFactory = DriverFactory::getInstance();

        return $driverFactory;
    }

    /**
     * Get a model by id
     *
     * @param string $id
     * @param bool $update
     * @return static|bool
     */
    public static function get(string $id, bool $update = false)
    {
        $registry = ModelRegistry::getInstance();
        $factory = static::getDriverFactory();

        /**
         * @var static $model
         */
        $model = new static($id);

        // try to get the model from the registry
        if (static::$registry) {
            if ($registryModel = $registry->get(static::getName(), $id)) {
                $model = $registryModel;
                if (!$update) {
                    return $model;
                }
            }
        }

        // try to get the model from cache
        if (static::$cache && !$update && $factory->assembleCacheDriver()->get($model)) {
            if (static::$registry) {
                $registry->save($model);
            }

            return $model;
        }

        // try to get the model from nosql database
        if (static::$nosql && $factory->assembleNoSQLDriver()->get($model)) {
            if (static::$registry) {
                $registry->save($model);
            }

            if (static::$cache) {
                $factory->assembleCacheDriver()->save($model);
            }

            return $model;
        }

        // try to get the model from relational database
        if (static::$relational && $factory->assembleRelationalDriver()->get($model)) {
            if (static::$registry) {
                $registry->save($model);
            }

            if (static::$cache) {
                $factory->assembleCacheDriver()->save($model);
            }

            if (static::$nosql) {
                $factory->assembleNoSQLDriver()->save($model);
            }

            return $model;
        }

        return false;
    }

    /**
     * Query the model
     *
     * @param Query $query
     * @return QueryResult
     */
    public static function query(Query $query): QueryResult
    {
        $factory = static::getDriverFactory();

        $query->modelClassName = static::class;

        /** @var QueryableDriverInterface $driver */
        $driver = null;
        if (static::$nosql) {
            $driver = $factory->assembleNoSQLDriver();
        } else if (static::$relational) {
            $driver = $factory->assembleRelationalDriver();
        } else {
            throw new \BadMethodCallException("You can't query the model if no queryable driver is enabled.");
        }

        /** @var QueryResult $result */
        $result = $driver->query($query);

        if (static::$registry) {
            if ($result->wasSuccessful() && count($result) > 0) {
                foreach ($result as $model) {
                    ModelRegistry::getInstance()->save($model);
                }
            }
        }

        return $result;
    }

    /**
     * Shorter or more readable way to write a select query
     *
     * GenericModel::select("a"=>"b");
     *   which is the same as
     * GenericModel::query((new SelectQuery())->where("a"=>"b"));
     *
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|null $fields
     * @param array|null|int|Limit $limit
     * @return QueryResult
     */
    public static function select($where = null, $order = null, $fields = null, $limit = null): QueryResult
    {
        return static::query(new SelectQuery($where, $order, $fields, $limit));
    }

    /**
     * Shorter or more readable way to write an update query
     *
     * @param array|null $fields
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|null|int|Limit $limit
     * @return QueryResult
     */
    public static function update($fields = null, $where = null, $order = null, $limit = null)
    {
        return static::query(new UpdateQuery($fields, $where, $order, $limit));
    }

    /**
     * Save the model changes
     *
     * @return bool
     */
    public function save(): bool
    {
        $factory = static::getDriverFactory();

        // new model, generate id and save in registry
        if (!$this->getId()) {
            $this->generateId();

            if (static::$registry) {
                ModelRegistry::getInstance()->save($this);
            }
        }

        // save in relational database
        if (static::$relational) {
            if (!$factory->assembleRelationalDriver()->save($this)) {
                return false;
            }
        }

        // save in nosql database
        if (static::$nosql) {
            if (!$factory->assembleNoSQLDriver()->save($this)) {
                return false;
            }
        }

        // save in search database
        if (static::$search) {
            if (!$factory->assembleSearchDriver()->save($this)) {
                return false;
            }
        }

        // save in cache
        if (static::$cache) {
            if (!$factory->assembleCacheDriver()->save($this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete the model
     *
     * @return bool
     */
    public function delete(): bool
    {
        $factory = static::getDriverFactory();
        $success = true;

        // delete in relational database
        if (static::$relational) {
            if (!$factory->assembleRelationalDriver()->delete($this)) {
                $success = false;
            }
        }

        // delete in nosql database
        if (static::$nosql) {
            if (!$factory->assembleNoSQLDriver()->delete($this)) {
                $success = false;
            }
        }

        // delete in search database
        if (static::$search) {
            if (!$factory->assembleSearchDriver()->delete($this)) {
                $success = false;
            }
        }

        // delete in cache
        if (static::$cache) {
            if (!$factory->assembleCacheDriver()->delete($this)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Return the cache time
     *
     * @return int
     */
    public function getCacheTime(): int
    {
        return static::$cache ?: 0;
    }
}