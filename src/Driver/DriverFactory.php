<?php

namespace Aternos\Model\Driver;

/**
 * Class DriverFactory
 *
 * @author Matthias Neid
 * @package Aternos\Model
 */
class DriverFactory
{
    /**
     * Registered drivers
     *
     * @var array
     */
    private $drivers = [
        "Cache" => "\\Aternos\\Model\\Driver\\Cache\\Redis",
        "NoSQL" => "\\Aternos\\Model\\Driver\\NoSQL\\Cassandra",
        "Registry" => "\\Aternos\\Model\\Driver\\Registry\\Property",
        "Relational" => "\\Aternos\\Model\\Driver\\Relational\\Mysqli",
        "Search" => "\\Aternos\\Model\\Driver\\Search\\Elasticsearch"
    ];

    /**
     * @var array
     */
    private $driverInstances = [];

    /**
     * Register a driver in the factory
     *
     * @param string $driver
     * @param string $class \Aternos\Model\DriverInterface
     */
    public function register($driver, $class)
    {
        $this->drivers[$driver] = $class;
    }

    /**
     * Assemble a driver in the factory or return an already assembled driver
     *
     * @param string $driver
     * @return DriverInterface
     */
    public function assembleDriver($driver): DriverInterface
    {
        if (!isset($this->driverInstances[$driver])) {
            $this->driverInstances[$driver] = new $this->drivers[$driver]();
        }

        return $this->driverInstances[$driver];
    }

    /**
     * Assemble a cache driver
     *
     * @return Cache\CacheDriverInterface
     */
    public function assembleCacheDriver()
    {
        /**
         * @var Cache\CacheDriverInterface $cacheDriver
         */
        $cacheDriver = $this->assembleDriver("Cache");

        return $cacheDriver;
    }

    /**
     * Assemble a nosql driver
     *
     * @return NoSQL\NoSQLDriverInterface
     */
    public function assembleNoSQLDriver()
    {
        /**
         * @var NoSQL\NoSQLDriverInterface $nosqlDriver
         */
        $nosqlDriver = $this->assembleDriver("NoSQL");

        return $nosqlDriver;
    }

    /**
     * Assemble a registry driver
     *
     * @return Registry\RegistryDriverInterface
     */
    public function assembleRegistryDriver()
    {
        /**
         * @var Registry\RegistryDriverInterface $registryDriver
         */
        $registryDriver = $this->assembleDriver("Registry");

        return $registryDriver;
    }

    /**
     * Assemble a relational driver
     *
     * @return Relational\RelationalDriverInterface
     */
    public function assembleRelationalDriver()
    {
        /**
         * @var Relational\RelationalDriverInterface $relationalDriver
         */
        $relationalDriver = $this->assembleDriver("Relational");

        return $relationalDriver;
    }

    /**
     * Assemble a search driver
     *
     * @return Search\SearchDriverInterface
     */
    public function assembleSearchDriver()
    {
        /**
         * @var Search\SearchDriverInterface $searchDriver
         */
        $searchDriver = $this->assembleDriver("Search");

        return $searchDriver;
    }

    /**
     * @var DriverFactory
     */
    protected static $instance;

    /**
     * @return DriverFactory
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    protected function __clone() {}
    protected function __construct() {}
}