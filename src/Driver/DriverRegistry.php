<?php

namespace Aternos\Model\Driver;

use Aternos\Model\Driver\Cassandra\Cassandra;
use Aternos\Model\Driver\Elasticsearch\Elasticsearch;
use Aternos\Model\Driver\Mysqli\Mysqli;
use Aternos\Model\Driver\Redis\Redis;
use InvalidArgumentException;

/**
 * Class DriverRegistry
 *
 * @package Aternos\Model\Driver
 */
class DriverRegistry implements DriverRegistryInterface
{
    /**
     * @var array|string[]
     */
    protected array $classes = [
        Cassandra::ID => Cassandra::class,
        Elasticsearch::ID => Elasticsearch::class,
        Mysqli::ID => Mysqli::class,
        Redis::ID => Redis::class
    ];

    /**
     * @var array<string, DriverInterface>
     */
    protected array $drivers = [];

    /**
     * Register a driver object by ID
     *
     * @param DriverInterface $driver
     */
    public function registerDriver(DriverInterface $driver)
    {
        $this->drivers[$driver->getId()] = $driver;
    }

    /**
     * Register a driver class by ID, a driver instance will only be created if necessary
     *
     * The class has to be preconfigured with all credentials etc., no parameters are passed to constructor
     *
     * @param string $id
     * @param string $class
     */
    public function registerDriverClass(string $id, string $class)
    {
        $this->classes[$id] = $class;
    }

    /**
     * Get a driver by ID, driver instances are preferred, classes will be used if necessary
     *
     * @param string $id
     * @return DriverInterface
     */
    public function getDriver(string $id): DriverInterface
    {
        if (isset($this->drivers[$id])) {
            return $this->drivers[$id];
        }

        if (isset($this->classes[$id])) {
            $class = $this->classes[$id];
            $driver = new $class();
            $this->registerDriver($driver);
            return $driver;
        }

        throw new InvalidArgumentException("Driver with ID '" . $id . "' not found.");
    }

    /**
     * Check if a driver is instance of a class (even if the driver has no instance yet)
     *
     * @param string $id
     * @param string $class
     * @return bool
     */
    public function isDriverInstanceOf(string $id, string $class): bool
    {
        if (isset($this->drivers[$id])) {
            return is_subclass_of($this->drivers[$id], $class);
        }

        if (isset($this->classes[$id])) {
            return is_subclass_of($this->classes[$id], $class);
        }

        throw new InvalidArgumentException("Driver with ID '" . $id . "' not found.");
    }

    /**
     * @var DriverRegistry|null
     */
    protected static ?DriverRegistry $instance = null;

    /**
     * @return DriverRegistry
     */
    public static function getInstance(): DriverRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Prohibited for singleton
     */
    protected function __clone()
    {
    }

    /**
     * Prohibited for singleton
     */
    protected function __construct()
    {
    }
}