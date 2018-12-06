<?php

namespace Aternos\Model;

use Aternos\Model\Driver\DriverFactory;

/**
 * Class Model
 *
 * @author Matthias Neid
 * @package Aternos\Model
 */
abstract class GenericModel extends BaseModel
{
    /**
     * @var string
     */
    protected $id;

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
     * Length of the random generated unique identifier
     *
     * @var int
     */
    protected static $idLength = 16;

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
     * @return Model|bool
     */
    public static function get(string $id, bool $update = false)
    {
        $class = get_called_class();
        $registry = ModelRegistry::getInstance();
        $factory = self::getDriverFactory();

        /**
         * @var Model $model
         */
        $model = new $class($id);

        // try to get the model from the registry
        if (self::$registry) {
            if ($registryModel = $registry->get(self::getName(), $id)) {
                $model = $registryModel;
                if (!$update) {
                    return $model;
                }
            }
        }

        // try to get the model from cache
        if (self::$cache && !$update && $factory->assembleCacheDriver()->get($model)) {
            if (self::$registry) {
                $registry->save($model);
            }

            return $model;
        }

        // try to get the model from nosql database
        if (self::$nosql && $factory->assembleNoSQLDriver()->get($model)) {
            if (self::$registry) {
                $registry->save($model);
            }

            if (self::$cache) {
                $factory->assembleCacheDriver()->save($model);
            }

            return $model;
        }

        // try to get the model from relational database
        if (self::$relational && $factory->assembleRelationalDriver()->get($model)) {
            if (self::$registry) {
                $registry->save($model);
            }

            if (self::$cache) {
                $factory->assembleCacheDriver()->save($model);
            }

            if (self::$nosql) {
                $factory->assembleNoSQLDriver()->save($model);
            }

            return $model;
        }

        return false;
    }

    /**
     * Model constructor.
     *
     * @param null|string $id
     */
    public function __construct($id = null)
    {
        $this->setId($id);
    }

    /**
     * Get the unique identifier of the model
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the unique identifier
     *
     * @param $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * Save the model changes
     *
     * @return bool
     */
    public function save(): bool
    {
        $factory = self::getDriverFactory();

        // new model, generate id and save in registry
        if (!$this->getId()) {
            $this->generateId();

            if (self::$registry) {
                ModelRegistry::getInstance()->save($this);
            }
        }

        // save in relational database
        if (self::$relational) {
            if (!$factory->assembleRelationalDriver()->save($this)) {
                return false;
            }
        }

        // save in nosql database
        if (self::$nosql) {
            if (!$factory->assembleNoSQLDriver()->save($this)) {
                return false;
            }
        }

        // save in search database
        if (self::$search) {
            if (!$factory->assembleSearchDriver()->save($this)) {
                return false;
            }
        }

        // save in cache
        if (self::$cache) {
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
        $factory = self::getDriverFactory();
        $success = true;

        // delete in relational database
        if (self::$relational) {
            if (!$factory->assembleRelationalDriver()->delete($this)) {
                $success = false;
            }
        }

        // delete in nosql database
        if (self::$nosql) {
            if (!$factory->assembleNoSQLDriver()->delete($this)) {
                $success = false;
            }
        }

        // delete in search database
        if (self::$search) {
            if (!$factory->assembleSearchDriver()->delete($this)) {
                $success = false;
            }
        }

        // delete in cache
        if (self::$cache) {
            if (!$factory->assembleCacheDriver()->delete($this)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Generate an unique identifier for the model
     */
    protected function generateId()
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        do {
            $id = '';
            for ($i = 0; $i < self::$idLength; $i++) {
                $id .= $characters[rand(0, $charactersLength - 1)];
            }
        } while (self::get($id));

        $this->setId($id);
    }
}