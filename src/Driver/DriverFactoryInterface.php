<?php

namespace Aternos\Model\Driver;

/**
 * Interface DriverFactoryInterface
 *
 * @package Aternos\Model\Driver
 */
interface DriverFactoryInterface
{
    /**
     * Return singleton instance of class
     *
     * @return DriverFactoryInterface
     */
    public static function getInstance();

    /**
     * Register a driver in the factory
     *
     * @param $driver
     * @param $class
     * @return bool
     */
    public function registerDriver($driver, $class): bool;

    /**
     * Assemble a driver in the factory or return an already assembled driver
     *
     * @param $driver
     * @return DriverInterface
     */
    public function assembleDriver($driver): DriverInterface;
}