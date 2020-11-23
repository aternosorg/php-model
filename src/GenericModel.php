<?php

namespace Aternos\Model;

use Aternos\Model\Driver\DriverRegistry;
use Aternos\Model\Driver\DriverRegistryInterface;
use Aternos\Model\Driver\Features\CacheableInterface;
use Aternos\Model\Driver\Features\DeletableInterface;
use Aternos\Model\Driver\Features\GettableInterface;
use Aternos\Model\Driver\Features\QueryableInterface;
use Aternos\Model\Driver\Features\SavableInterface;
use Aternos\Model\Driver\Mysqli\Mysqli;
use Aternos\Model\Driver\Redis\Redis;
use BadMethodCallException;
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
    protected static bool $registry = true;

    /**
     * Is this model cacheable and if yes, for how long (seconds)
     *
     * @var ?int
     */
    protected static ?int $cache = null;

    /**
     * Driver IDs ordered for a regular get by ID
     *
     * Caches etc. first, database after that, search etc. at the end
     *
     * @var array|string[]
     */
    protected static array $drivers = [
        Redis::ID,
        Mysqli::ID
    ];

    /**
     * Driver IDs ordered for saving
     *
     * Set this to null to use the reverse order of static::$drivers which implement SavableInterface
     *
     * @var array|null
     */
    protected static ?array $saveDrivers = null;

    /**
     * Driver IDs ordered for deleting
     *
     * Set this to null to use the reverse order of static::$drivers which implement DeletableInterface
     *
     * @var array|null
     */
    protected static ?array $deleteDrivers = null;

    /**
     * Driver IDs ordered for querying
     *
     * Set this to null to use the same order as static::$drivers which implement QueryableInterface
     *
     * @var array|null
     */
    protected static ?array $queryDrivers = null;


    /**
     * Get the driver factory for the current model
     *
     * @return DriverRegistryInterface
     */
    protected static function getDriverRegistry(): DriverRegistryInterface
    {
        return DriverRegistry::getInstance();
    }

    /**
     * Get all gettable drivers from static::$drivers
     *
     * @return array
     */
    protected static function getGettableDrivers(): array
    {
        $drivers = [];
        foreach (static::$drivers as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, GettableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }

    /**
     * Get all savable drivers from static::$saveDrivers or static::$drivers
     *
     * @return array
     */
    protected static function getSavableDrivers(): array
    {
        $drivers = [];
        foreach (static::$saveDrivers ?? array_reverse(static::$drivers) as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, SavableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }

    /**
     * Get all savable drivers from static::$saveDrivers or static::$drivers
     *
     * @return array
     */
    protected static function getDeletableDrivers(): array
    {
        $drivers = [];
        foreach (static::$deleteDrivers ?? array_reverse(static::$drivers) as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, DeletableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }

    /**
     * Get all savable drivers from static::$saveDrivers or static::$drivers
     *
     * @return array
     */
    protected static function getQueryableDrivers(): array
    {
        $drivers = [];
        foreach (static::$queryDrivers ?? static::$drivers as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, QueryableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
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
        $driverRegistry = static::getDriverRegistry();

        // try to get the model from the registry
        if (static::$registry) {
            if ($registryModel = $registry->get(static::getName(), $id)) {
                $model = $registryModel;
                if (!$update) {
                    return $model;
                }
            }
        }

        $model = new static($id);

        $cacheDrivers = [];
        $success = false;
        foreach (static::getGettableDrivers() as $gettableDriver) {
            /** @var GettableInterface $driver */
            $driver = $driverRegistry->getDriver($gettableDriver);
            if ($update && $driver instanceof CacheableInterface) {
                $cacheDrivers[] = $driver;
                continue;
            }

            if ($driver->get($model)) {
                $success = true;
                break;
            }

            if ($driver instanceof CacheableInterface) {
                $cacheDrivers[] = $driver;
            }
        }

        if (!$success) {
            return false;
        }

        if (static::$cache) {
            foreach ($cacheDrivers as $cacheDriver) {
                if ($cacheDriver instanceof SavableInterface) {
                    $cacheDriver->save($model);
                }
            }
        }

        if (static::$registry) {
            $registry->save($model);
        }

        return $model;
    }

    /**
     * Query the model
     *
     * @param Query $query
     * @return QueryResult|static[]
     */
    public static function query(Query $query): QueryResult
    {
        $query->modelClassName = static::class;

        $result = false;
        foreach (static::getQueryableDrivers() as $queryableDriver) {
            /** @var QueryableInterface $driver */
            $driver = static::getDriverRegistry()->getDriver($queryableDriver);
            $result = $driver->query($query);

            if ($result->wasSuccessful()) {
                break;
            }
        }

        if ($result === false) {
            throw new BadMethodCallException("You can't query the model if no queryable driver is available.");
        }

        if (static::$registry) {
            if ($result->wasSuccessful() && count($result) > 0) {
                foreach ($result as $model) {
                    if ($model->getId() === null) {
                        continue;
                    }
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
     * @return QueryResult|static[]
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
        // new model, generate id and save in registry
        if (!$this->getId()) {
            $this->generateId();

            if (static::$registry) {
                ModelRegistry::getInstance()->save($this);
            }
        }

        foreach (static::getSavableDrivers() as $savableDriver) {
            /** @var SavableInterface $driver */
            $driver = static::getDriverRegistry()->getDriver($savableDriver);
            if (!$driver->save($this)) {
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
        $success = true;

        foreach (static::getDeletableDrivers() as $deletableDriver) {
            /** @var DeletableInterface $driver */
            $driver = static::getDriverRegistry()->getDriver($deletableDriver);
            if (!$driver->delete($this)) {
                $success = false;
            }
        }

        // delete in registry
        if (static::$registry) {
            ModelRegistry::getInstance()->delete($this);
        }

        return $success;
    }

    /**
     * Set multiple fields declared by data on the model
     *
     * Directly updates the database, can be used for
     * partial updates without rewriting the whole
     * dataset
     *
     * @param array $data
     * @return QueryResult
     */
    public function set(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return static::update($data, [static::$idField => $this->{static::$idField}]);
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