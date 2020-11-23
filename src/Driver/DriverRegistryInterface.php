<?php

namespace Aternos\Model\Driver;

/**
 * Interface DriverRegistryInterface
 *
 * @package Aternos\Model\Driver
 */
interface DriverRegistryInterface
{
    /**
     * Register a driver object by ID
     *
     * @param DriverInterface $driver
     */
    public function registerDriver(DriverInterface $driver);

    /**
     * Register a driver class by ID, a driver instance will only be created if necessary
     *
     * The class has to be preconfigured with all credentials etc., no parameters are passed to constructor
     *
     * @param string $id
     * @param string $class
     */
    public function registerDriverClass(string $id, string $class);

    /**
     * Get a driver by ID, driver instances are preferred, classes will be used if necessary
     *
     * @param string $id
     * @return DriverInterface
     */
    public function getDriver(string $id): DriverInterface;

    /**
     * Check if a driver is instance of a class (even if the driver has no instance yet)
     *
     * @param string $id
     * @param string $class
     * @return bool
     */
    public function isDriverInstanceOf(string $id, string $class): bool;

    /**
     * @return DriverRegistryInterface
     */
    public static function getInstance(): DriverRegistryInterface;
}