<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\ModelInterface;

/**
 * Interface GettableInterface
 *
 * @package Aternos\Model\Driver\Features
 */
interface GettableInterface
{
    /**
     * Get the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function get(ModelInterface $model): bool;
}