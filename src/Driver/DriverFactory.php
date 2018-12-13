<?php

namespace Aternos\Model\Driver;

use Aternos\Model\Driver\Cache\CacheDriverInterface;
use Aternos\Model\Driver\NoSQL\NoSQLDriverInterface;
use Aternos\Model\Driver\Relational\RelationalDriverInterface;
use Aternos\Model\Driver\Search\SearchDriverInterface;

/**
 * Class DriverFactory
 *
 * @package Aternos\Model
 */
class DriverFactory implements DriverFactoryInterface
{
    /**
     * Registered drivers
     *
     * @var array
     */
    protected $drivers = [
        "Cache" => "\\Aternos\\Model\\Driver\\Cache\\Redis",
        "NoSQL" => "\\Aternos\\Model\\Driver\\NoSQL\\Cassandra",
        "Relational" => "\\Aternos\\Model\\Driver\\Relational\\Mysqli",
        "Search" => "\\Aternos\\Model\\Driver\\Search\\Elasticsearch"
    ];

    /**
     * @var array
     */
    protected $driverInstances = [];

    /**
     * Register a driver in the factory
     *
     * @param string $driver
     * @param string $class \Aternos\Model\DriverInterface
     * @return bool
     */
    public function registerDriver($driver, $class): bool
    {
        $this->drivers[$driver] = $class;

        return true;
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
     * @return CacheDriverInterface
     */
    public function assembleCacheDriver(): CacheDriverInterface
    {
        /**
         * @var CacheDriverInterface $cacheDriver
         */
        $cacheDriver = $this->assembleDriver("Cache");

        return $cacheDriver;
    }

    /**
     * Assemble a nosql driver
     *
     * @return NoSQLDriverInterface
     */
    public function assembleNoSQLDriver(): NoSQLDriverInterface
    {
        /**
         * @var NoSQLDriverInterface $nosqlDriver
         */
        $nosqlDriver = $this->assembleDriver("NoSQL");

        return $nosqlDriver;
    }

    /**
     * Assemble a relational driver
     *
     * @return RelationalDriverInterface
     */
    public function assembleRelationalDriver(): RelationalDriverInterface
    {
        /**
         * @var RelationalDriverInterface $relationalDriver
         */
        $relationalDriver = $this->assembleDriver("Relational");

        return $relationalDriver;
    }

    /**
     * Assemble a search driver
     *
     * @return SearchDriverInterface
     */
    public function assembleSearchDriver(): SearchDriverInterface
    {
        /**
         * @var SearchDriverInterface $searchDriver
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
    public static function getInstance(): DriverFactory
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