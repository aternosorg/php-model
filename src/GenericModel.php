<?php

namespace Aternos\Model;

use Aternos\Model\Driver\DriverInterface;
use Aternos\Model\Driver\DriverRegistry;
use Aternos\Model\Driver\DriverRegistryInterface;
use Aternos\Model\Driver\Features\{CacheableInterface,
    DeletableInterface,
    DeleteQueryableInterface,
    GettableInterface,
    QueryableInterface,
    SavableInterface,
    SearchableInterface,
    SelectQueryableInterface,
    UpdateQueryableInterface};
use Aternos\Model\Driver\Mysqli\Mysqli;
use Aternos\Model\Driver\Redis\Redis;
use Aternos\Model\Search\Search;
use Aternos\Model\Search\SearchResult;
use BadMethodCallException;
use Aternos\Model\Query\{DeleteQuery,
    GroupField,
    Limit,
    Query,
    QueryResult,
    QueryResultCollection,
    SelectQuery,
    UpdateQuery,
    WhereCondition,
    WhereGroup};

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
     * Driver IDs ordered for a regular get/select by ID
     *
     * Caches etc. first, database after that, search etc. at the end
     *
     * @var class-string<DriverInterface>[]
     */
    protected static array $drivers = [
        Redis::ID,
        Mysqli::ID
    ];

    /**
     * Driver IDs ordered for saving/updating
     *
     * Set this to null to use the reverse order of static::$drivers which implement SavableInterface
     *
     * @var class-string<SavableInterface>[]|null
     */
    protected static ?array $saveDrivers = null;

    /**
     * Driver IDs ordered for deleting
     *
     * Set this to null to use the reverse order of static::$drivers which implement DeletableInterface
     *
     * @var class-string<DeletableInterface>[]|null
     */
    protected static ?array $deleteDrivers = null;

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
     * @return class-string<GettableInterface>[]
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
     * Get all select queryable drivers from static::$drivers
     *
     * @return class-string<SelectQueryableInterface>[]
     */
    protected static function getSelectQueryableDrivers(): array
    {
        $drivers = [];
        foreach (static::$drivers as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, SelectQueryableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }

    /**
     * Get all savable drivers from static::$saveDrivers or static::$drivers
     *
     * @return class-string<SavableInterface>[]
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
     * Get all update queryable drivers from static::$saveDrivers or static::$drivers
     *
     * @return class-string<UpdateQueryableInterface>[]
     */
    protected static function getUpdateQueryableDrivers(): array
    {
        $drivers = [];
        foreach (static::$saveDrivers ?? array_reverse(static::$drivers) as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, UpdateQueryableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }

    /**
     * Get all savable drivers from static::$deleteDrivers or static::$drivers
     *
     * @return class-string<DeletableInterface>[]
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
     * Get all delete queryable drivers from static::$deleteDrivers or static::$drivers
     *
     * @return class-string<DeleteQueryableInterface>[]
     */
    protected static function getDeleteQueryableDrivers(): array
    {
        $drivers = [];
        foreach (static::$deleteDrivers ?? array_reverse(static::$drivers) as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, DeleteQueryableInterface::class)) {
                $drivers[] = $driver;
            }
        }
        return $drivers;
    }

    /**
     * Get all searchable drivers from static::$drivers
     *
     * @return class-string<SearchableInterface>[]
     */
    protected static function getSearchableDrivers(): array
    {
        $drivers = [];
        foreach (static::$drivers as $driver) {
            if (static::getDriverRegistry()->isDriverInstanceOf($driver, SearchableInterface::class)) {
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
     * @return static|null
     */
    public static function get(string $id, bool $update = false): ?static
    {
        $registry = ModelRegistry::getInstance();
        $driverRegistry = static::getDriverRegistry();

        // try to get the model from the registry
        if (static::$registry) {
            if ($registryModel = $registry->get(static::class, $id)) {
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
            return null;
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
     * @return QueryResult<static>
     */
    public static function query(Query $query): QueryResult
    {
        $query->modelClassName = static::class;

        if ($query instanceof SelectQuery) {
            $drivers = static::getSelectQueryableDrivers();
        } else if ($query instanceof UpdateQuery) {
            $drivers = static::getUpdateQueryableDrivers();
        } else if ($query instanceof DeleteQuery) {
            $drivers = static::getDeleteQueryableDrivers();
        } else {
            throw new BadMethodCallException("This is not a valid query (Select/Update/Delete).");
        }

        $result = false;
        $results = [];
        foreach ($drivers as $queryableDriver) {
            /** @var QueryableInterface $driver */
            $driver = static::getDriverRegistry()->getDriver($queryableDriver);
            $result = $driver->query($query);

            if ($result->wasSuccessful() && $query instanceof SelectQuery) {
                break;
            }

            if (!$query instanceof SelectQuery) {
                $results[] = $result;
            }
        }

        if ($result === false) {
            throw new BadMethodCallException("You can't query the model if no queryable driver is available.");
        }

        if (static::$registry) {
            if ($query instanceof SelectQuery && $result->wasSuccessful() && count($result) > 0) {
                foreach ($result as $model) {
                    if ($model->getId() === null) {
                        continue;
                    }
                    ModelRegistry::getInstance()->save($model);
                }
            }
        }

        if ($query instanceof SelectQuery || count($results) === 1) {
            return $result;
        } else {
            return new QueryResultCollection(true, $results);
        }
    }

    /**
     * Shorter or more readable way to write a select query
     *
     * GenericModel::select("a"=>"b");
     *   which is the same as
     * GenericModel::query((new SelectQuery())->where("a"=>"b"));
     *
     * @param array|WhereCondition|WhereGroup|null $where
     * @param array|null $order
     * @param array|null $fields
     * @param array|int|Limit|null $limit
     * @param array|GroupField[]|string[]|null $group
     * @return QueryResult<static>
     */
    public static function select(null|WhereCondition|array|WhereGroup $where = null,
                                  null|array $order = null,
                                  null|array $fields = null,
                                  null|Limit|array|int $limit = null,
                                  null|array $group = null): QueryResult
    {
        return static::query(new SelectQuery($where, $order, $fields, $limit, $group));
    }

    /**
     * Shorter or more readable way to write an update query
     *
     * @param array|null $fields
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|int|Limit|null $limit
     * @return QueryResult<static>
     */
    public static function update(null|array                           $fields = null,
                                  null|array|WhereCondition|WhereGroup $where = null,
                                  null|array                           $order = null,
                                  null|array|int|Limit                 $limit = null): QueryResult
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
     * @return QueryResult<static>
     */
    public function set(array $data): QueryResult
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

    /**
     * Search the model
     *
     * @param Search $search
     * @return SearchResult<static>
     */
    public static function search(Search $search): SearchResult
    {
        $search->setModelClassName(static::class);

        $result = false;
        foreach (static::getSearchableDrivers() as $searchableDriver) {
            /** @var SearchableInterface $driver */
            $driver = static::getDriverRegistry()->getDriver($searchableDriver);
            $result = $driver->search($search);

            if ($result->wasSuccessful()) {
                break;
            }
        }

        if ($result === false) {
            throw new BadMethodCallException("You can't search the model if no searchable driver is available.");
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
}