<?php

namespace Aternos\Model\Driver;

/**
 * Interface DriverInterface
 *
 * @package Aternos\Model
 */
interface DriverInterface
{
    /**
     * Get a unique ID for this driver
     *
     * @return string
     */
    public function getId(): string;
}