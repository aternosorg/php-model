<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\ModelInterface;

/**
 * Interface Savable
 *
 * @package Aternos\Model\Driver\Features
 */
interface SavableInterface extends DriverFeatureInterface
{
    /**
     * Save the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function save(ModelInterface $model): bool;
}