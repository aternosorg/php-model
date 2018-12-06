<?php

namespace Aternos\Model\Driver;

use \Aternos\Model\Model;

/**
 * Interface DriverInterface
 *
 * @author Matthias Neid
 * @package Aternos\Model
 */
interface DriverInterface
{
    /**
     * Save the model
     *
     * @param Model $model
     * @return bool
     */
    public function save(Model $model): bool;

    /**
     * Get the model
     *
     * @param Model $model
     * @return bool
     */
    public function get(Model $model): bool;

    /**
     * Delete the model
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool;
}