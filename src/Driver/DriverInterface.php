<?php

namespace Aternos\Model\Driver;

use \Aternos\Model\ModelInterface;

/**
 * Interface DriverInterface
 *
 * @package Aternos\Model
 */
interface DriverInterface
{
    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function save(ModelInterface $model): bool;

    /**
     * Get the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function get(ModelInterface $model): bool;

    /**
     * Delete the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function delete(ModelInterface $model): bool;
}